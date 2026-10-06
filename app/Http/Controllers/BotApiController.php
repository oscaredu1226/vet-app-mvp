<?php

namespace App\Http\Controllers;

use App\Models\BotReservationRequest;
use App\Models\Cliente;
use App\Models\Evento;
use App\Models\Mascota;
use App\Rules\HalfHourAppointment;
use App\Services\AppointmentAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** API del negocio. Telegram, audios e IA se ejecutan en el proyecto externo. */
class BotApiController extends Controller
{
    public function services()
    {
        return response()->json([
            'services' => config('bot_api.services'), 'timezone' => config('app.timezone'),
            'hours' => ['from' => '08:00', 'to' => '20:00', 'interval_minutes' => 30],
        ]);
    }

    public function customer(Request $request)
    {
        $request->validate(['phone' => 'required|string|max:20']);
        $client = $this->findCustomer($request->phone);
        return response()->json(['customer' => $client ? [
            'id' => $client->id_cliente, 'nombre' => $client->nombre, 'apellido' => $client->apellido,
            'pets' => $client->mascotas()->where('estado', 'Activo')->get(['id_mascota', 'nombre'])->map(fn ($pet) => [
                'id' => $pet->id_mascota, 'name' => $pet->nombre,
            ]),
        ] : null]);
    }

    public function availability(Request $request)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d|after_or_equal:today']);
        $occupied = Evento::whereDate('fecha', $request->date)->where('estado', 'pendiente')->whereNotNull('hora')->pluck('hora')->all();
        $slots = [];
        for ($minute = 480; $minute <= 1200; $minute += 30) {
            $time = sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
            if (!in_array($time . ':00', $occupied, true) && Carbon::parse($request->date . ' ' . $time)->isFuture()) $slots[] = $time;
        }
        return response()->json(['date' => $request->date, 'slots' => $slots]);
    }

    public function preview(Request $request)
    {
        $request->validate(['request_id' => 'required|uuid', 'chat_id' => 'required|integer|min:1']);
        $input = $request->except('request_id');
        ksort($input);
        $hash = hash('sha256', json_encode($input, JSON_THROW_ON_ERROR));
        $existing = BotReservationRequest::find($request->request_id);
        if ($existing) {
            abort_unless((string) $existing->chat_id === (string) $request->chat_id && hash_equals($existing->payload_hash, $hash), 409, 'La solicitud cambió. Prepara un nuevo resumen.');
            return response()->json($this->result($existing));
        }
        $data = $this->prepare($request->all());
        if (Evento::whereDate('fecha', $data['date'])->where('hora', $data['time'])->where('estado', 'pendiente')->exists()) {
            throw ValidationException::withMessages(['time' => 'Ese horario ya está ocupado. Selecciona otro.']);
        }
        // UUID proporcionado por el bot: el reintento de HTTP conserva la misma solicitud.
        $reservation = BotReservationRequest::firstOrCreate(['id' => $request->request_id], [
            'chat_id' => $request->chat_id, 'payload_hash' => $hash, 'payload' => $data,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(config('bot_api.confirmation_minutes')),
        ]);
        abort_unless((string) $reservation->chat_id === (string) $request->chat_id && hash_equals($reservation->payload_hash, $hash), 409);
        return response()->json($this->result($reservation));
    }

    public function confirm(Request $request, BotReservationRequest $reservation, AppointmentAvailability $availability)
    {
        $request->validate(['chat_id' => 'required|integer|min:1']);
        abort_unless((string) $reservation->chat_id === (string) $request->chat_id, 404);
        return DB::transaction(function () use ($reservation, $availability) {
            $reservation = BotReservationRequest::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->status === 'confirmed') {
                abort_unless($reservation->id_evento !== null, 410, 'El personal retiró esta cita del calendario.');
                return response()->json($this->result($reservation));
            }
            abort_unless($reservation->status === 'pending' && $reservation->expires_at->isFuture(), 410, 'La solicitud venció o fue descartada. Pide un nuevo resumen.');
            $data = $this->prepare($reservation->payload);
            $client = $data['client_id'] ? Cliente::findOrFail($data['client_id']) : Cliente::create([
                'nombre' => $data['nombre'], 'apellido' => $data['apellido'], 'celular' => $data['phone'],
            ]);
            $pet = $data['pet_id'] ? Mascota::where('id_cliente', $client->id_cliente)->findOrFail($data['pet_id']) : Mascota::create([
                'id_cliente' => $client->id_cliente, 'nombre' => $data['pet_name'], 'especie' => $data['especie'],
                'raza' => $data['raza'], 'genero' => $data['genero'], 'fecha_nacimiento' => $data['fecha_nacimiento'],
                'esterilizado' => $data['esterilizado'], 'estado' => 'Activo',
            ]);
            $event = $availability->save([
                'id_mascota' => $pet->id_mascota, 'fecha' => $data['date'], 'hora' => $data['time'],
                'titulo' => mb_substr(config('bot_api.services')[$data['service']]['name'] . ' - ' . $pet->nombre, 0, 255),
                'descripcion' => 'Reserva confirmada por el cliente desde el bot externo de Telegram.', 'estado' => 'pendiente',
            ]);
            $reservation->update(['status' => 'confirmed', 'id_evento' => $event->id_evento]);
            return response()->json($this->result($reservation));
        });
    }

    public function cancel(Request $request, BotReservationRequest $reservation)
    {
        $request->validate(['chat_id' => 'required|integer|min:1']);
        abort_unless((string) $reservation->chat_id === (string) $request->chat_id, 404);
        return DB::transaction(function () use ($reservation) {
            $reservation = BotReservationRequest::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            abort_if($reservation->status === 'confirmed', 409, 'La cita ya está confirmada; solicita el cambio al personal.');
            $reservation->update(['status' => 'cancelled']);
            return response()->json(['status' => 'cancelled']);
        });
    }

    private function prepare(array $input): array
    {
        Validator::make($input, [
            'chat_id' => 'required|integer|min:1', 'phone' => 'required|string|max:20',
            'service' => ['required', Rule::in(array_keys(config('bot_api.services')))],
            'pet_name' => 'required|string|max:255', 'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => ['bail', 'required', 'date_format:H:i', new HalfHourAppointment],
        ])->validate();
        $phone = $this->phone($input['phone']);
        if (strlen($phone) < 9 || strlen($phone) > 15) throw ValidationException::withMessages(['phone' => 'Comparte un celular válido.']);
        $client = $this->findCustomer($phone);
        $matches = $client ? $client->mascotas()->where('estado', 'Activo')->get()->filter(fn ($pet) => mb_strtolower(trim($pet->nombre)) === mb_strtolower(trim($input['pet_name']))) : collect();
        if ($matches->count() > 1) abort(409, 'Hay varias mascotas con ese nombre. Contacta al personal.');
        $pet = $matches->first();
        $rules = [];
        if (!$client) $rules += ['nombre' => 'required|string|max:255', 'apellido' => 'required|string|max:255'];
        if (!$pet) $rules += [
            'especie' => 'required|string|max:50', 'raza' => 'required|string|max:100', 'genero' => 'required|in:Macho,Hembra',
            'fecha_nacimiento' => 'required|date_format:Y-m-d|before_or_equal:today|after_or_equal:' . now()->subYears(30)->toDateString(),
            'esterilizado' => 'required|in:Si,No',
        ];
        Validator::make($input, $rules, ['required' => 'Falta :attribute para preparar la reserva.'])->validate();
        if (!Carbon::parse($input['date'] . ' ' . $input['time'])->isFuture()) throw ValidationException::withMessages(['date' => 'Selecciona una fecha y hora futuras.']);
        $data = array_intersect_key($input, array_flip(['chat_id', 'service', 'pet_name', 'date', 'time', 'nombre', 'apellido', 'especie', 'raza', 'genero', 'fecha_nacimiento', 'esterilizado']));
        $data['phone'] = $phone;
        $data['client_id'] = $client?->id_cliente;
        $data['pet_id'] = $pet?->id_mascota;
        if ($client) { $data['nombre'] = $client->nombre; $data['apellido'] = $client->apellido; }
        if ($pet) $data['pet_name'] = $pet->nombre;
        return $data;
    }

    private function result(BotReservationRequest $reservation): array
    {
        $data = $reservation->payload;
        return ['request_id' => $reservation->id, 'status' => $reservation->status,
                'expires_at' => $reservation->expires_at->toIso8601String(), 'event_id' => $reservation->id_evento,
                'summary' => [
                    'owner' => $data['nombre'] . ' ' . $data['apellido'], 'pet' => $data['pet_name'],
                    'service' => config('bot_api.services')[$data['service']]['name'], 'date' => $data['date'], 'time' => $data['time'],
                    'new_customer' => !$data['client_id'], 'new_pet' => !$data['pet_id'],
                    'pet_data' => array_intersect_key($data, array_flip(['especie', 'raza', 'genero', 'fecha_nacimiento', 'esterilizado'])),
                ]];
    }

    private function findCustomer(string $phone): ?Cliente
    {
        $normalized = $this->phone($phone);
        $matches = Cliente::all()->filter(fn ($client) => $this->phone($client->celular) === $normalized);
        if ($matches->count() > 1) abort(409, 'El número coincide con varios registros. Contacta al personal.');
        return $matches->first();
    }

    private function phone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) === 11 && str_starts_with($digits, '51') ? substr($digits, 2) : $digits;
    }
}
