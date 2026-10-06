<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentAvailability
{
    /** Un cupo por horario. El bloqueo por día evita confirmaciones simultáneas. */
    public function save(array $data, ?Evento $event = null): Evento
    {
        return DB::transaction(function () use ($data, $event) {
            $date = $data['fecha'] ?? $event?->fecha;
            $time = $data['hora'] ?? $event?->hora;
            if (array_key_exists('hora', $data) && !$data['hora']) $time = null;
            if ($time && ($data['estado'] ?? $event?->estado ?? 'pendiente') === 'pendiente') {
                DB::table('appointment_day_locks')->insertOrIgnore(['fecha' => $date]);
                DB::table('appointment_day_locks')->where('fecha', $date)->lockForUpdate()->first();
                $occupied = Evento::whereDate('fecha', $date)->where('hora', $time)->where('estado', 'pendiente');
                if ($event) $occupied->where('id_evento', '!=', $event->id_evento);
                if ($occupied->exists()) {
                    throw ValidationException::withMessages(['hora' => 'Ese horario ya está ocupado. Selecciona otro.']);
                }
            }
            if ($event) { $event->update($data); return $event; }
            return Evento::create($data);
        });
    }
}
