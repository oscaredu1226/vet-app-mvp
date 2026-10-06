<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Cirugia;
use App\Models\TipoCirugia;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CirugiaController extends Controller
{
    public function create(Mascota $mascota)
    {
        $mascota->load('cliente');
        // Historial de cirugías previas
        $historial = $mascota->hasMany(Cirugia::class, 'id_mascota')->orderBy('fecha', 'desc')->get();
        return view('cirugias.create', compact('mascota', 'historial'));
    }

    public function store(Request $request, Mascota $mascota)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'diagnostico' => 'required|string',
            'peso_pre' => 'required|numeric|min:0',
            'temperatura_pre' => 'required|numeric',
            'fc_pre' => 'required|integer',
            'fr_pre' => 'required|integer',
            'tipo_cirugia' => 'required|string',
            'cirujanos_data' => 'required|json', // Validamos que venga el JSON de médicos
            'anestesistas_data' => 'required|json',
            'tratamiento_aplicado' => 'nullable|string',
            'peso_post' => 'required|numeric',
            'temperatura_post' => 'required|numeric',
            'fc_post' => 'required|integer',
            'fr_post' => 'required|integer',
            'proxima_cita' => 'nullable|date',
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            DB::beginTransaction();

            // Guardar nuevo tipo de cirugía si no existe en catálogo
            $tipoNombre = $request->tipo_cirugia;
            if (!TipoCirugia::where('nombre', $tipoNombre)->exists()) {
                TipoCirugia::create(['nombre' => $tipoNombre]);
            }

            $cirugia = Cirugia::create([
                'id_mascota' => $mascota->id_mascota,
                'fecha' => $request->fecha,
                'medico' => auth()->user()->name . ' ' . auth()->user()->apellido,
                'motivo' => $request->input('motivo', 'Cirugía'),
                'diagnostico' => $request->diagnostico,
                'observaciones_pre' => $request->observaciones_pre,
                'ayuno' => $request->has('ayuno'), // Checkbox
                
                // Pre
                'peso_pre' => $request->peso_pre,
                'temperatura_pre' => $request->temperatura_pre,
                'fc_pre' => $request->fc_pre,
                'fr_pre' => $request->fr_pre,
                'tlc_pre' => $request->tlc_pre,
                'hidratacion_pre' => $request->hidratacion_pre,

                // Intra
                'tipo_cirugia' => $tipoNombre,
                'cirujanos' => json_decode($request->cirujanos_data),
                'observaciones_cirujanos' => $request->observaciones_cirujanos,
                'anestesistas' => json_decode($request->anestesistas_data),
                'tratamiento_aplicado' => $request->tratamiento_aplicado,
                'observaciones_tratamiento' => $request->observaciones_tratamiento,

                // Post
                'peso_post' => $request->peso_post,
                'temperatura_post' => $request->temperatura_post,
                'fc_post' => $request->fc_post,
                'fr_post' => $request->fr_post,
                'proxima_cita' => $request->proxima_cita,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
            ]);

            // Crear Evento de seguimiento (próxima cita o automático al día siguiente)
            if ($request->proxima_cita) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Control post-quirúrgico programado';
                
                Evento::create([
                    'id_mascota' => $mascota->id_mascota,
                    'fecha' => $request->proxima_cita,
                    'titulo' => 'Control Post-Quirúrgico - ' . $mascota->nombre,
                    'descripcion' => $descripcionEvento,
                    'estado' => 'pendiente'
                ]);
            } else {
                Evento::create([
                    'id_mascota' => $mascota->id_mascota,
                    'fecha' => \Carbon\Carbon::parse($request->fecha)->addDays(1),
                    'titulo' => 'Control Post-Quirúrgico - ' . $mascota->nombre,
                    'descripcion' => 'Revisión tras cirugía: ' . $tipoNombre,
                    'estado' => 'pendiente'
                ]);
            }

            // Asociar archivos temporales si existen
            \App\Models\ArchivoHistoria::asociarArchivosTemporales(
                session()->getId(), 
                'cirugia', 
                $cirugia->id_cirugia
            );

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Cirugía registrada correctamente',
                'cirugia_id' => $cirugia->id_cirugia,
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit($mascotaId, $id)
    {
        $cirugia = Cirugia::findOrFail($id);
        $mascota = Mascota::findOrFail($mascotaId);
        $historial = Cirugia::where('id_mascota', $mascotaId)
            ->orderBy('fecha', 'desc')
            ->get();
        
        return view('cirugias.edit', compact('cirugia', 'mascota', 'historial'));
    }

    public function update(Request $request, $mascotaId, $id)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'diagnostico' => 'required|string',
            'peso_pre' => 'required|numeric|min:0',
            'temperatura_pre' => 'required|numeric',
            'fc_pre' => 'required|integer',
            'fr_pre' => 'required|integer',
            'tipo_cirugia' => 'required|string',
            'cirujanos_data' => 'required|json',
            'anestesistas_data' => 'required|json',
            'tratamiento_aplicado' => 'nullable|string',
            'peso_post' => 'required|numeric',
            'temperatura_post' => 'required|numeric',
            'fc_post' => 'required|integer',
            'fr_post' => 'required|integer',
            'proxima_cita' => 'nullable|date',
            'mensaje_proxima_cita' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            DB::beginTransaction();

            $cirugia = Cirugia::findOrFail($id);
            $mascota = Mascota::findOrFail($mascotaId);

            // Actualizar tipo de cirugía si no existe en catálogo
            $tipoNombre = $request->tipo_cirugia;
            if (!TipoCirugia::where('nombre', $tipoNombre)->exists()) {
                TipoCirugia::create(['nombre' => $tipoNombre]);
            }

            $cirugia->update([
                'fecha' => $request->fecha,
                'motivo' => $request->input('motivo', 'Cirugía'),
                'diagnostico' => $request->diagnostico,
                'observaciones_pre' => $request->observaciones_pre,
                'ayuno' => $request->has('ayuno'),
                
                // Pre
                'peso_pre' => $request->peso_pre,
                'temperatura_pre' => $request->temperatura_pre,
                'fc_pre' => $request->fc_pre,
                'fr_pre' => $request->fr_pre,
                'tlc_pre' => $request->tlc_pre,
                'hidratacion_pre' => $request->hidratacion_pre,

                // Intra
                'tipo_cirugia' => $tipoNombre,
                'cirujanos' => json_decode($request->cirujanos_data),
                'observaciones_cirujanos' => $request->observaciones_cirujanos,
                'anestesistas' => json_decode($request->anestesistas_data),
                'tratamiento_aplicado' => $request->tratamiento_aplicado,
                'observaciones_tratamiento' => $request->observaciones_tratamiento,

                // Post
                'peso_post' => $request->peso_post,
                'temperatura_post' => $request->temperatura_post,
                'fc_post' => $request->fc_post,
                'fr_post' => $request->fr_post,
                'proxima_cita' => $request->proxima_cita,
                'mensaje_proxima_cita' => $request->mensaje_proxima_cita,
            ]);
            
            // Crear o actualizar evento si hay próxima cita
            if ($request->proxima_cita) {
                $descripcionEvento = $request->mensaje_proxima_cita 
                                     ? $request->mensaje_proxima_cita 
                                     : 'Control post-quirúrgico programado';
                
                $eventoExistente = Evento::where('id_mascota', $mascota->id_mascota)
                    ->where('titulo', 'LIKE', '%Control Post-Quirúrgico%')
                    ->where('fecha', $request->proxima_cita)
                    ->first();
                
                if (!$eventoExistente) {
                    Evento::create([
                        'id_mascota' => $mascota->id_mascota,
                        'fecha' => $request->proxima_cita,
                        'titulo' => 'Control Post-Quirúrgico - ' . $mascota->nombre,
                        'descripcion' => $descripcionEvento,
                        'estado' => 'pendiente'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Cirugía actualizada correctamente',
                'redirect' => route('historia.show', $mascota->id_mascota)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($mascotaId, $id)
    {
        try {
            $cirugia = Cirugia::findOrFail($id);
            $cirugia->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Cirugía eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarTipos(Request $request)
    {
        $query = $request->query('query');
        $tipos = TipoCirugia::where('nombre', 'LIKE', "%{$query}%")->limit(10)->get();
        return response()->json($tipos);
    }
}