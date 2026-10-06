<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Vacuna;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VacunaController extends Controller
{
    /**
     * Muestra el formulario para crear una nueva vacuna.
     * [cite_start]Reemplaza el HTML de 'modules/vacuna.php' [cite: 1419-1474]
     */
    public function create(Mascota $mascota)
    {
        $mascota->load('cliente');
        $historial_vacunas = $mascota->vacunas()->orderBy('fecha', 'desc')->get();
        return view('vacunas.create', compact('mascota', 'historial_vacunas'));
    }

    /**
     * Guarda un nuevo registro de vacuna.
     * [cite_start]Reemplaza la lógica POST de 'modules/vacuna.php' [cite: 1415-1418]
     */
    public function store(Request $request, Mascota $mascota)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'vacuna_aplicada' => 'required|string',
            'peso' => 'nullable|numeric|min:0',
            'temperatura' => 'required|numeric',
            'laboratorio' => 'nullable|string',
            'lote' => 'nullable|string',
            'vencimiento' => 'nullable|date',
            'via_administracion' => 'nullable|string',
            'dosis' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'proxima_dosis' => 'nullable|date',
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $datos = $validator->validated();
            $datos['fecha'] = $request->fecha;
            $datos['medico'] = auth()->user()->name . ' ' . auth()->user()->apellido;
            
            $vacuna = $mascota->vacunas()->create($datos);

            if ($request->proxima_dosis) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Vacuna de refuerzo - ' . $mascota->nombre;

                Evento::create([
                    'id_mascota' => $mascota->id_mascota,
                    'fecha' => $request->proxima_dosis,
                    'titulo' => 'Vacuna - ' . $mascota->nombre,
                    'descripcion' => $descripcionEvento,
                    'estado' => 'pendiente'
                ]);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Vacuna registrada correctamente',
                'vacuna_id' => $vacuna->id_vacuna
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario para editar una vacuna.
     */
    public function edit($mascotaId, $id)
    {
        $vacuna = Vacuna::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        $historial_vacunas = Vacuna::where('id_mascota', $mascotaId)
            ->orderBy('fecha', 'desc')
            ->get();
        
        return view('vacunas.edit', compact('vacuna', 'mascota', 'historial_vacunas'));
    }

    /**
     * Actualiza una vacuna existente.
     */
    public function update(Request $request, $mascotaId, $id)
    {
        $vacuna = Vacuna::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        
        $validator = Validator::make($request->all(), [
            'vacuna_aplicada' => 'required|string',
            'temperatura' => 'required|numeric',
            'proxima_dosis' => 'nullable|date',
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $vacuna->update([
                'fecha' => $request->fecha,
                'peso' => $request->peso,
                'temperatura' => $request->temperatura,
                'hidratacion' => $request->hidratacion,
                'tlc_tiempo_llenado' => $request->tlc_tiempo_llenado,
                'anamnesis' => $request->anamnesis,
                'vacuna_aplicada' => $request->vacuna_aplicada,
                'laboratorio' => $request->laboratorio,
                'lote' => $request->lote,
                'vencimiento' => $request->vencimiento,
                'via_administracion' => $request->via_administracion,
                'dosis' => $request->dosis,
                'proxima_dosis' => $request->proxima_dosis,
                'tipo_proxima_vacuna' => $request->tipo_proxima_vacuna,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
                'observaciones' => $request->observaciones,
            ]);
            
            // Crear o actualizar evento si hay próxima dosis
            if ($request->proxima_dosis) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Vacuna de refuerzo programada';
                
                $eventoExistente = Evento::where('id_mascota', $mascota->id_mascota)
                    ->where('titulo', 'LIKE', '%Vacuna%')
                    ->where('fecha', $request->proxima_dosis)
                    ->first();
                
                if (!$eventoExistente) {
                    Evento::create([
                        'id_mascota' => $mascota->id_mascota,
                        'fecha' => $request->proxima_dosis,
                        'titulo' => 'Vacuna - ' . $mascota->nombre,
                        'descripcion' => $descripcionEvento,
                        'estado' => 'pendiente'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Vacuna actualizada correctamente',
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una vacuna.
     */
    public function destroy($id)
    {
        try {
            $vacuna = Vacuna::findOrFail($id);
            $vacuna->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Vacuna eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}