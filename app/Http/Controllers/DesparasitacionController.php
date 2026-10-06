<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Desparasitacion;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DesparasitacionController extends Controller
{
    /**
     * Muestra el formulario para una nueva desparasitación.
     * Reemplaza el HTML de 'modules/desparasitacion.php' [cite: 604-616]
     */
    public function create(Mascota $mascota)
    {
        $mascota->load('cliente');
        $historial = $mascota->desparasitaciones()->orderBy('fecha', 'desc')->get();
        return view('desparasitaciones.create', compact('mascota', 'historial'));
    }

    /**
     * Guarda un nuevo registro de desparasitación.
     * Reemplaza la lógica POST de 'modules/desparasitacion.php'
     */
    public function store(Request $request, Mascota $mascota)
    {
        $validator = Validator::make($request->all(), [
            'producto' => 'required|string',
            'peso' => 'required|numeric|min:0',
            'temperatura' => 'nullable|numeric',
            'laboratorio' => 'nullable|string',
            'dosis' => 'nullable|string',
            'via_administracion' => 'nullable|string',
            'tipo_parasito' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'proxima_cita' => 'nullable|date',
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $desparasitacion = $mascota->desparasitaciones()->create([
                'fecha' => $request->fecha,
                'medico' => auth()->user()->name . ' ' . auth()->user()->apellido,
                'peso' => $request->peso,
                'temperatura' => $request->temperatura,
                'producto' => $request->producto,
                'laboratorio' => $request->laboratorio,
                'dosis' => $request->dosis,
                'via_administracion' => $request->via_administracion,
                'tipo_parasito' => $request->tipo_parasito,
                'observaciones' => $request->observaciones,
                'proxima_cita' => $request->proxima_cita,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
            ]);

            if ($request->proxima_cita) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Desparasitación de control programada';

                Evento::create([
                    'id_mascota' => $mascota->id_mascota,
                    'fecha' => $request->proxima_cita,
                    'titulo' => 'Control de Desparasitación - ' . $mascota->nombre,
                    'descripcion' => $descripcionEvento,
                    'estado' => 'pendiente'
                ]);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Desparasitación registrada correctamente',
                'desparasitacion_id' => $desparasitacion->id_desparasitacion,
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario para editar una desparasitación.
     */
    public function edit($mascotaId, $id)
    {
        $desparasitacion = Desparasitacion::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        $historial = Desparasitacion::where('id_mascota', $mascotaId)
            ->orderBy('fecha', 'desc')
            ->get();
        
        return view('desparasitaciones.edit', compact('desparasitacion', 'mascota', 'historial'));
    }

    /**
     * Actualiza una desparasitación existente.
     */
    public function update(Request $request, $mascotaId, $id)
    {
        $desparasitacion = Desparasitacion::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'producto' => 'required|string',
            'peso' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $mascota = Mascota::findOrFail($mascotaId);
            
            $desparasitacion->update([
                'fecha' => $request->fecha,
                'peso' => $request->peso,
                'temperatura' => $request->temperatura,
                'frecuencia_cardiaca' => $request->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $request->frecuencia_respiratoria,
                'tlc' => $request->tlc,
                'hidratacion' => $request->hidratacion,
                'producto' => $request->producto,
                'laboratorio' => $request->laboratorio,
                'dosis' => $request->dosis,
                'via_administracion' => $request->via_administracion,
                'tipo_parasito' => $request->tipo_parasito,
                'proxima_cita' => $request->proxima_cita,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
                'observaciones' => $request->observaciones,
            ]);
            
            // Crear o actualizar evento si hay próxima cita
            if ($request->proxima_cita) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Desparasitación de control programada';
                
                $eventoExistente = Evento::where('id_mascota', $mascota->id_mascota)
                    ->where('titulo', 'LIKE', '%Control de Desparasitación%')
                    ->where('fecha', $request->proxima_cita)
                    ->first();
                
                if (!$eventoExistente) {
                    Evento::create([
                        'id_mascota' => $mascota->id_mascota,
                        'fecha' => $request->proxima_cita,
                        'titulo' => 'Control de Desparasitación - ' . $mascota->nombre,
                        'descripcion' => $descripcionEvento,
                        'estado' => 'pendiente'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Desparasitación actualizada correctamente',
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una desparasitación.
     */
    public function destroy($id)
    {
        try {
            $desparasitacion = Desparasitacion::findOrFail($id);
            $desparasitacion->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Desparasitación eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}