<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Consulta;
use App\Models\Evento;
use App\Rules\HalfHourAppointment;
use App\Services\AppointmentAvailability;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    /**
     * Muestra el formulario para crear una nueva consulta.
     */
    public function create(Mascota $mascota)
    {
        $mascota->load('cliente');
        $historial_consultas = $mascota->consultas()->orderBy('fecha', 'desc')->get();
        
        return view('consultas.create', compact('mascota', 'historial_consultas'));
    }

    /**
     * Guarda una nueva consulta.
     */
    public function store(Request $request, Mascota $mascota)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'motivo' => 'nullable|string',
            'temperatura' => 'nullable|numeric|min:0',
            'peso' => 'required|numeric|min:0.01',
            'frecuencia_cardiaca' => 'nullable|integer',
            'frecuencia_respiratoria' => 'nullable|integer',
            'tlc_tiempo_llenado' => 'nullable|string',
            'hidratacion' => 'nullable|string',
            'anamnesis' => 'nullable|string',
            'examen_fisico' => 'nullable|string',
            'diagnostico' => 'required|string',
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |             'examenes' => 'nullable|string',
            'plan_tratamiento' => 'nullable|string',
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |             'receta' => 'nullable|string',
            'observaciones' => 'nullable|string',
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
            'proxima_cita' => ['bail', 'nullable', 'date', new HalfHourAppointment],
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
            'mensaje_proxima_cita' => 'nullable|string', // Nuevo campo validado
        ], [
            'peso.required' => 'El peso es obligatorio',
            'peso.min' => 'El peso debe ser mayor a 0',
            'diagnostico.required' => 'El diagnóstico es obligatorio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            $datos = [
                'id_mascota' => $mascota->id_mascota,
                'fecha' => $request->fecha,
                'medico' => auth()->user()->name . ' ' . auth()->user()->apellido,
                'motivo' => $request->input('motivo', 'Consulta'),
                'peso' => $request->peso,
                'temperatura' => $request->temperatura,
                'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
                'tlc_tiempo_llenado' => $request->tlc_tiempo_llenado,
                'hidratacion' => $request->hidratacion,
                'anamnesis' => $request->anamnesis,
                'examen_fisico' => $request->examen_fisico,
                'diagnostico' => $request->diagnostico,
                'plan_tratamiento' => $request->plan_tratamiento,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'examenes' => $request->examenes,
                'tratamiento' => $request->tratamiento,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'receta' => $request->receta,
                'observaciones' => $request->observaciones,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'proxima_cita' => $request->proxima_cita,
            ];
            
            // Guardar próxima cita para crear evento
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |             $proximaCita = $request->proxima_cita;
            
            $datos['proxima_cita'] = $request->proxima_cita;
            $datos['mensaje_proxima_cita'] = $request->mensaje_proxima_cita;

            // Crear la consulta
            $consulta = Consulta::create($datos);
            $this->guardarCita($consulta, $mascota, $request);
            
            // Si hay próxima cita, crear evento
            // MVP_POSTERIOR: Calendario de proximas citas
// MVP_POSTERIOR | if ($proximaCita) {
// MVP_POSTERIOR |                 // Lógica del mensaje: Si el usuario escribió algo, úsalo. Si no, usa el default.
// MVP_POSTERIOR |                 $descripcionEvento = $request->mensaje_proxima_cita 
// MVP_POSTERIOR |                                      ? $request->mensaje_proxima_cita 
// MVP_POSTERIOR |                                      : 'Consulta de control programada';
// MVP_POSTERIOR | 
// MVP_POSTERIOR |                 Evento::create([
// MVP_POSTERIOR |                     'id_mascota' => $mascota->id_mascota,
// MVP_POSTERIOR |                     'fecha' => $proximaCita,
// MVP_POSTERIOR |                     'titulo' => 'Consulta de Control - ' . $mascota->nombre,
// MVP_POSTERIOR |                     'descripcion' => $descripcionEvento, // Usamos la variable dinámica
// MVP_POSTERIOR |                     'estado' => 'pendiente'
// MVP_POSTERIOR |                 ]);
// MVP_POSTERIOR |             }
            
            // Asociar archivos temporales si existen
            // MVP_POSTERIOR: Asociacion de archivos adjuntos
// MVP_POSTERIOR | \App\Models\ArchivoHistoria::asociarArchivosTemporales(
// MVP_POSTERIOR |                 session()->getId(), 
// MVP_POSTERIOR |                 'consulta', 
// MVP_POSTERIOR |                 $consulta->id_consulta
// MVP_POSTERIOR |             );
            
            DB::commit();
            
            return response()->json([
                'success' => true, 
// MVP_POSTERIOR: Mensaje con calendario
// MVP_POSTERIOR |                 'message' => 'Consulta registrada correctamente' . ($proximaCita ? ' y evento creado para próxima cita' : ''),
                'message' => 'Consulta registrada correctamente',
                'consulta_id' => $consulta->id_consulta,
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el formulario para editar una consulta.
     */
    public function edit($mascotaId, $id)
    {
        $consulta = Consulta::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        $historial_consultas = Consulta::where('id_mascota', $mascotaId)
            ->orderBy('fecha', 'desc')
            ->get();
        
        return view('consultas.edit', compact('consulta', 'mascota', 'historial_consultas'));
    }

    /**
     * Actualiza una consulta existente.
     */
    public function update(Request $request, $mascotaId, $id)
    {
        $consulta = Consulta::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'peso' => 'required|numeric|min:0.01',
            'diagnostico' => 'required|string',
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
            'proxima_cita' => ['bail', 'nullable', 'date', new HalfHourAppointment($consulta->proxima_cita)],
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            DB::beginTransaction();
            
            $consulta->update([
                'fecha' => $request->fecha,
                'motivo' => $request->input('motivo', 'Consulta'),
                'peso' => $request->peso,
                'temperatura' => $request->temperatura,
                'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
                'tlc_tiempo_llenado' => $request->tlc_tiempo_llenado,
                'hidratacion' => $request->hidratacion,
                'anamnesis' => $request->anamnesis,
                'examen_fisico' => $request->examen_fisico,
                'diagnostico' => $request->diagnostico,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'examenes' => $request->examenes,
                'plan_tratamiento' => $request->plan_tratamiento,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'receta' => $request->receta,
                'observaciones' => $request->observaciones,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'proxima_cita' => $request->proxima_cita,
// MVP_POSTERIOR: Campos para versiones posteriores; conservar datos existentes
// MVP_POSTERIOR |                 'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
            ]);
            
            $consulta->update($request->only(['proxima_cita', 'mensaje_proxima_cita']));
            if ($request->has('proxima_cita')) {
                $this->guardarCita($consulta, $mascota, $request);
            }

            // Crear o actualizar evento si hay próxima cita
            // MVP_POSTERIOR: Calendario de proximas citas
// MVP_POSTERIOR | if ($request->proxima_cita) {
// MVP_POSTERIOR |                 $descripcionEvento = $request->mensaje_proxima_cita 
// MVP_POSTERIOR |                                      ? $request->mensaje_proxima_cita 
// MVP_POSTERIOR |                                      : 'Consulta de control programada';
// MVP_POSTERIOR |                 
// MVP_POSTERIOR |                 // Verificar si ya existe un evento para esta consulta
// MVP_POSTERIOR |                 $eventoExistente = Evento::where('id_mascota', $mascota->id_mascota)
// MVP_POSTERIOR |                     ->where('titulo', 'LIKE', '%Control de Consulta%')
// MVP_POSTERIOR |                     ->where('fecha', $request->proxima_cita)
// MVP_POSTERIOR |                     ->first();
// MVP_POSTERIOR |                 
// MVP_POSTERIOR |                 if (!$eventoExistente) {
// MVP_POSTERIOR |                     Evento::create([
// MVP_POSTERIOR |                         'id_mascota' => $mascota->id_mascota,
// MVP_POSTERIOR |                         'fecha' => $request->proxima_cita,
// MVP_POSTERIOR |                         'titulo' => 'Control de Consulta - ' . $mascota->nombre,
// MVP_POSTERIOR |                         'descripcion' => $descripcionEvento,
// MVP_POSTERIOR |                         'estado' => 'pendiente'
// MVP_POSTERIOR |                     ]);
// MVP_POSTERIOR |                 }
// MVP_POSTERIOR |             }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Consulta actualizada correctamente',
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /** Una consulta mantiene una sola cita de control, incluso al reprogramarla. */
    private function guardarCita(Consulta $consulta, Mascota $mascota, Request $request): void
    {
        if (!$request->filled('proxima_cita')) return;
        $fecha = \Carbon\Carbon::parse($request->proxima_cita);
        $evento = Evento::where('id_consulta', $consulta->id_consulta)->first();
        app(AppointmentAvailability::class)->save([
            'id_consulta' => $consulta->id_consulta,
            'id_mascota' => $mascota->id_mascota,
            'fecha' => $fecha->toDateString(),
            'hora' => $fecha->format('H:i:s'),
            'titulo' => 'Consulta de Control - ' . $mascota->nombre,
            'descripcion' => $request->mensaje_proxima_cita ?: 'Consulta de control programada',
        ], $evento);
    }

    /**
     * Elimina una consulta.
     */
    public function destroy($id)
    {
        try {
            $consulta = Consulta::findOrFail($id);
            $consulta->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Consulta eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
