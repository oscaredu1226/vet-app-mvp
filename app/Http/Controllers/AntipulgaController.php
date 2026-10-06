<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Antipulga;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AntipulgaController extends Controller
{
    /**
     * Muestra el formulario para un nuevo antipulgas.
     */
    public function create(Mascota $mascota)
    {
        $mascota->load('cliente');
        $historial = $mascota->antipulgas()->orderBy('fecha', 'desc')->get();
        return view('antipulgas.create', compact('mascota', 'historial'));
    }

    /**
     * Guarda un nuevo registro de antipulgas.
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
            'observaciones' => 'nullable|string',
            'proxima_cita' => 'nullable|date',
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $antipulga = $mascota->antipulgas()->create([
                'fecha' => $request->fecha,
                'medico' => auth()->user()->name . ' ' . auth()->user()->apellido,
                'peso' => $request->peso,
                'temperatura' => $request->temperatura,
                'producto' => $request->producto,
                'laboratorio' => $request->laboratorio,
                'dosis' => $request->dosis,
                'via_administracion' => $request->via_administracion,
                'observaciones' => $request->observaciones,
                'proxima_cita' => $request->proxima_cita,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
            ]);

            if ($request->proxima_cita) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Antipulgas de control programada';

                Evento::create([
                    'id_mascota' => $mascota->id_mascota,
                    'fecha' => $request->proxima_cita,
                    'titulo' => 'Control de Antipulgas - ' . $mascota->nombre,
                    'descripcion' => $descripcionEvento,
                    'estado' => 'pendiente'
                ]);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Antipulgas registrado correctamente',
                'antipulga_id' => $antipulga->id_antipulgas,
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario para editar un antipulgas.
     */
    public function edit($mascotaId, $id)
    {
        $antipulga = Antipulga::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        $historial = Antipulga::where('id_mascota', $mascotaId)
            ->orderBy('fecha', 'desc')
            ->get();
        
        return view('antipulgas.edit', compact('antipulga', 'mascota', 'historial'));
    }

    /**
     * Actualiza un antipulgas existente.
     */
    public function update(Request $request, $mascotaId, $id)
    {
        $antipulga = Antipulga::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'producto' => 'required|string',
            'peso' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $mascota = Mascota::findOrFail($mascotaId);
            
            $antipulga->update([
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
                'proxima_cita' => $request->proxima_cita,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
                'observaciones' => $request->observaciones,
            ]);
            
            // Crear o actualizar evento si hay próxima cita
            if ($request->proxima_cita) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Antipulgas de control programada';
                
                $eventoExistente = Evento::where('id_mascota', $mascota->id_mascota)
                    ->where('titulo', 'LIKE', '%Control de Antipulgas%')
                    ->where('fecha', $request->proxima_cita)
                    ->first();
                
                if (!$eventoExistente) {
                    Evento::create([
                        'id_mascota' => $mascota->id_mascota,
                        'fecha' => $request->proxima_cita,
                        'titulo' => 'Control de Antipulgas - ' . $mascota->nombre,
                        'descripcion' => $descripcionEvento,
                        'estado' => 'pendiente'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Antipulgas actualizado correctamente',
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un antipulgas.
     */
    public function destroy($id)
    {
        try {
            $antipulga = Antipulga::findOrFail($id);
            $antipulga->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Antipulgas eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
