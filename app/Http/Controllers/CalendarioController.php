<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Mascota;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Rules\HalfHourAppointment;
use App\Services\AppointmentAvailability;
use Illuminate\Validation\ValidationException;

class CalendarioController extends Controller
{
    /**
     * Muestra la página del calendario y los eventos.
     * [cite_start]Reemplaza 'modules/calendario.php' [cite: 328-347]
     */
    public function index(Request $request)
    {
        // Fuente del calendario mensual; conserva las rutas y la lista por día.
        if ($request->boolean('feed')) {
            $request->validate([
                'start' => 'required|date',
                'end' => 'required|date|after:start',
            ]);
            return response()->json(Evento::whereDate('fecha', '>=', Carbon::parse($request->start)->toDateString())
                ->whereDate('fecha', '<', Carbon::parse($request->end)->toDateString())
                ->get()->map(fn ($evento) => [
                    'id' => $evento->id_evento,
                    'title' => $evento->titulo,
                    'start' => $evento->fecha . ($evento->hora ? 'T' . $evento->hora : ''),
                    'allDay' => !$evento->hora,
                    'color' => $evento->estado === 'completado' ? '#6c757d' : '#198754',
                ]));
        }
        $fecha_seleccionada = $request->query('fecha', Carbon::today()->toDateString());
        $request->validate(['fecha' => 'sometimes|date_format:Y-m-d']);
        
        $eventos = Evento::with(['mascota.cliente'])
                    ->whereDate('fecha', $fecha_seleccionada)
                    ->orderBy('hora')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        $total_pendientes = Evento::where('estado', 'pendiente')->count();

        // Obtener productos próximos a vencer (30 días o menos)
// MVP_POSTERIOR |         $productosProximosAVencer = Producto::whereNotNull('fecha_vencimiento')
// MVP_POSTERIOR |             ->whereDate('fecha_vencimiento', '>=', Carbon::today())
// MVP_POSTERIOR |             ->whereDate('fecha_vencimiento', '<=', Carbon::today()->addDays(30))
// MVP_POSTERIOR |             ->get();
        $productosProximosAVencer = collect();

        // Si es una petición AJAX (para cargar solo la lista de eventos)
        if ($request->ajax()) {
            return view('eventos.partials.lista_eventos', [
                'eventos_list' => $eventos,
                'fecha_seleccionada' => $fecha_seleccionada,
                'productosProximosAVencer' => $productosProximosAVencer
            ]);
        }

        // Carga la página completa
        return view('eventos.index', compact('eventos', 'fecha_seleccionada', 'total_pendientes', 'productosProximosAVencer'));
    }

    /**
     * Muestra un evento específico (para el modal de edición).
     * [cite_start]Reemplaza 'modules/obtener_evento.php' [cite: 1274]
     */
    public function show(Evento $evento)
    {
        $evento->load(['mascota.cliente']);
        return response()->json(['success' => true, 'evento' => $evento]);
    }

    /**
     * Guarda un nuevo evento.
     * [cite_start]Reemplaza 'modules/acciones_evento.php' (accion=crear) [cite: 207-209]
     */
    public function store(Request $request)
    {
        // Convertir fecha de DD-MM-YYYY a YYYY-MM-DD
        $fechaInput = $request->input('fecha');
        if ($fechaInput && preg_match('/^\d{2}-\d{2}-\d{4}$/', $fechaInput)) {
            $partes = explode('-', $fechaInput);
            $request->merge(['fecha' => $partes[2] . '-' . $partes[1] . '-' . $partes[0]]);
        }

// MVP_POSTERIOR |         // Combinar fecha con hora si se proporciona
// MVP_POSTERIOR |         if ($request->has('hora') && $request->input('hora')) {
// MVP_POSTERIOR |             $fecha = $request->input('fecha');
// MVP_POSTERIOR |             $hora = $request->input('hora');
// MVP_POSTERIOR |             $request->merge(['fecha' => $fecha . ' ' . $hora . ':00']);
// MVP_POSTERIOR |         }
// MVP_POSTERIOR | 

        $validator = Validator::make($request->all(), [
            'id_mascota' => 'required|integer|exists:mascotas,id_mascota',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:' . Carbon::today()->toDateString(),
            'hora' => ['bail', 'nullable', 'date_format:H:i', new HalfHourAppointment],
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'nullable|string|max:100',
        ], [
            'fecha.after_or_equal' => 'La fecha debe ser igual o posterior al día de hoy.',
            'id_mascota.required' => 'Debe seleccionar una mascota.',
            'titulo.required' => 'El título es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $datos = $validator->validated();
            $datos['estado'] = 'pendiente';
            // Se conserva la creación original; ahora comparte disponibilidad con Telegram.
            // Evento::create($datos);
            app(AppointmentAvailability::class)->save($datos);
            return response()->json(['success' => true, 'message' => 'Evento creado exitosamente']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un evento (editar o completar).
     * [cite_start]Reemplaza 'modules/acciones_evento.php' (accion=editar, accion=completar) [cite: 210-213]
     */
    public function update(Request $request, Evento $evento)
    {
        // Lógica para marcar como completado
        if ($request->has('accion') && $request->input('accion') === 'completar') {
            $evento->update(['estado' => 'completado']);
            return response()->json(['success' => true, 'message' => 'Evento marcado como completado']);
        }

        $fechaInput = $request->input('fecha');
        if ($fechaInput && preg_match('/^\d{2}-\d{2}-\d{4}$/', $fechaInput)) {
            $partes = explode('-', $fechaInput);
            $request->merge(['fecha' => $partes[2] . '-' . $partes[1] . '-' . $partes[0]]);
        }
        // Lógica para editar el evento
        $validator = Validator::make($request->all(), [
            'id_mascota' => 'required|integer|exists:mascotas,id_mascota',
            'fecha' => 'required|date_format:Y-m-d|after_or_equal:today',
            'hora' => ['bail', 'nullable', 'date_format:H:i', new HalfHourAppointment($evento->hora)],
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            // $evento->update($validator->validated());
            app(AppointmentAvailability::class)->save($validator->validated(), $evento);
            return response()->json(['success' => true, 'message' => 'Evento actualizado exitosamente']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un evento.
     * [cite_start]Reemplaza 'modules/acciones_evento.php' (accion=eliminar) [cite: 214]
     */
    public function destroy(Evento $evento)
    {
        try {
            $evento->delete();
            return response()->json(['success' => true, 'message' => 'Evento eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Devuelve el contador de eventos pendientes del día de hoy.
     * Reemplaza 'modules/contador_eventos.php'
     */
    public function contadorEventos()
    {
        $contador = Evento::where('estado', 'pendiente')
                          ->whereDate('fecha', Carbon::today())
                          ->count();
        return response()->json(['success' => true, 'count' => $contador]);
    }
}
