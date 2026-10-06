<?php

namespace App\Http\Controllers;

use App\Models\ArchivoHistoria;
use App\Http\Requests\StoreArchivoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ArchivoHistoriaController extends Controller
{
    /**
     * Subir archivo con procesamiento optimizado (nuevo método)
     * 
     * @param StoreArchivoRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeConProcesamiento(StoreArchivoRequest $request)
    {
        try {
            $archivo = $request->file('archivo');
            $mimeType = $archivo->getMimeType();
            $extension = $archivo->getClientOriginalExtension();
            
            // Generar nombre único usando UUID
            $nombreUnico = Str::uuid() . '.' . $extension;
            
            // Determinar si es imagen o documento
            $esImagen = in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
            
            if ($esImagen) {
                // Procesar imagen con Intervention Image
                $imagen = Image::read($archivo);
                
                // Redimensionar manteniendo aspecto (máximo 1200px de ancho)
                $imagen->scale(width: 1200);
                
                // Codificar a JPG con calidad 80%
                $imagenEncoded = $imagen->toJpeg(quality: 80);
                
                // Cambiar extensión a jpg
                $nombreUnico = Str::uuid() . '.jpg';
                
                // Guardar en disco public
                Storage::disk('public')->put('uploads/' . $nombreUnico, (string) $imagenEncoded);
                
                $rutaFinal = 'uploads/' . $nombreUnico;
                $tipoArchivo = 'imagen';
                
            } else {
                // Es un documento, guardar tal cual
                $rutaFinal = $archivo->storeAs('uploads', $nombreUnico, 'public');
                $tipoArchivo = 'documento';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'data' => [
                    'path' => $rutaFinal,
                    'nombre_archivo' => $nombreUnico,
                    'tipo' => $tipoArchivo,
                    'url' => Storage::disk('public')->url($rutaFinal)
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al subir archivo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir archivo a una historia médica (método original)
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('ArchivoHistoriaController::store - Request data:', [
            'all' => $request->all(),
            'files' => $request->allFiles(),
            'historia_type' => $request->historia_type,
            'historia_id' => $request->historia_id,
            'has_file' => $request->hasFile('archivo'),
            'headers' => $request->headers->all(),
            'csrf_token' => $request->input('_token'),
            'session_token' => csrf_token()
        ]);

        // Validación del archivo (permitir uploads temporales sin historia_id)
        $validationRules = [
            'archivo' => [
                'required',
                'file',
                'max:51200', // 50MB en KB
                'mimes:jpg,jpeg,png,pdf,mp4,mov,avi'
            ],
            'historia_type' => 'required|string|in:consulta,vacuna,desparasitacion,antipulga,cirugia',
        ];

        // Solo validar historia_id si se proporciona (no es temporal)
        if ($request->filled('historia_id')) {
            $validationRules['historia_id'] = 'required|integer|exists:' . $this->getTableName($request->historia_type) . ',' . $this->getPrimaryKey($request->historia_type);
        }

        $validator = Validator::make($request->all(), $validationRules, [
            'archivo.required' => 'Debe seleccionar un archivo',
            'archivo.max' => 'El archivo no puede superar los 50MB',
            'archivo.mimes' => 'Solo se permiten archivos JPG, PNG, PDF, MP4, MOV, AVI',
        ]);

        if ($validator->fails()) {
            \Log::error('ArchivoHistoriaController::store - Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            $archivo = $request->file('archivo');
            \Log::info('ArchivoHistoriaController::store - File info:', [
                'original_name' => $archivo->getClientOriginalName(),
                'size' => $archivo->getSize(),
                'mime_type' => $archivo->getMimeType()
            ]);
            
            // Determinar tipo de archivo
            $tipoArchivo = $this->determinarTipoArchivo($archivo->getMimeType());
            
            // Generar nombre único
            $nombreArchivo = $this->generarNombreUnico($archivo, $request->historia_type, $request->historia_id);
            
            // Crear ruta con subcarpetas por año/mes
            $fecha = now();
            $rutaCarpeta = "historias/{$fecha->year}/{$fecha->month}";
            
            // Guardar archivo en storage/app/public/historias/año/mes/
            $ruta = $archivo->storeAs($rutaCarpeta, $nombreArchivo, 'public');
            \Log::info('ArchivoHistoriaController::store - File stored:', [
                'ruta' => $ruta,
                'nombre_archivo' => $nombreArchivo,
                'tipo_archivo' => $tipoArchivo,
                'carpeta' => $rutaCarpeta
            ]);
            
            // Guardar información en base de datos
            $archivoHistoria = ArchivoHistoria::create([
                'historia_type' => $request->historia_type,
                'historia_id' => $request->historia_id ?? null, // Permitir null para archivos temporales
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_archivo' => $nombreArchivo,
                'ruta' => $ruta,
                'tipo_mime' => $archivo->getMimeType(),
                'tipo_archivo' => $tipoArchivo,
                'tamaño' => $archivo->getSize(),
                'session_id' => $request->historia_id ? null : session()->getId(), // Usar session_id para temporales
                'is_temporal' => !$request->filled('historia_id') // Marcar como temporal si no hay historia_id
            ]);

            \Log::info('ArchivoHistoriaController::store - Database record created:', [
                'archivo_id' => $archivoHistoria->id,
                'historia_type' => $archivoHistoria->historia_type,
                'historia_id' => $archivoHistoria->historia_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'archivo' => [
                    'id' => $archivoHistoria->id,
                    'nombre' => $archivoHistoria->nombre_original,
                    'tipo' => $archivoHistoria->tipo_archivo,
                    'tamaño' => $archivoHistoria->tamaño_formateado,
                    'url' => $archivoHistoria->url
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('ArchivoHistoriaController::store - Exception:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener archivos de una historia médica (incluye temporales si no hay historia_id)
     */
    public function getArchivos(Request $request)
    {
        if ($request->filled('historia_id')) {
            // Obtener archivos de una historia específica
            $archivos = ArchivoHistoria::where('historia_type', $request->historia_type)
                ->where('historia_id', $request->historia_id)
                ->where('is_temporal', false)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Obtener archivos temporales de la sesión actual
            $archivos = ArchivoHistoria::where('historia_type', $request->historia_type)
                ->where('session_id', session()->getId())
                ->where('is_temporal', true)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'archivos' => $archivos->map(function ($archivo) {
                return [
                    'id' => $archivo->id,
                    'nombre' => $archivo->nombre_original,
                    'tipo' => $archivo->tipo_archivo,
                    'tamaño' => $archivo->tamaño_formateado,
                    'url' => $archivo->url,
                    'fecha' => $archivo->created_at->format('d/m/Y H:i')
                ];
            })
        ]);
    }

    /**
     * Eliminar archivo
     */
    public function destroy($id)
    {
        try {
            $archivo = ArchivoHistoria::findOrFail($id);
            
            // Eliminar archivo físico
            if (Storage::disk('public')->exists($archivo->ruta)) {
                Storage::disk('public')->delete($archivo->ruta);
            }
            
            // Eliminar registro de BD
            $archivo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el archivo'
            ], 500);
        }
    }

    /**
     * Determinar tipo de archivo basado en MIME type
     */
    private function determinarTipoArchivo($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'imagen';
        } elseif ($mimeType === 'application/pdf') {
            return 'documento';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        return 'documento'; // Por defecto
    }

    /**
     * Generar nombre único para el archivo
     */
    private function generarNombreUnico($archivo, $tipoHistoria, $historiaId)
    {
        $extension = $archivo->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $randomString = \Str::random(6);
        $historiaIdPart = $historiaId ? $historiaId : 'temp';
        
        return "{$tipoHistoria}_{$historiaIdPart}_{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Obtener nombre de tabla según tipo de historia
     */
    private function getTableName($tipoHistoria)
    {
        $tablas = [
            'consulta' => 'consultas',
            'vacuna' => 'vacunas', 
            'desparasitacion' => 'desparasitaciones',
            'antipulga' => 'antipulgas',
            'cirugia' => 'cirugias'
        ];
        
        return $tablas[$tipoHistoria] ?? 'consultas';
    }

    /**
     * Obtener clave primaria según tipo de historia
     */
    private function getPrimaryKey($tipoHistoria)
    {
        $claves = [
            'consulta' => 'id_consulta',
            'vacuna' => 'id_vacuna',
            'desparasitacion' => 'id_desparasitacion', 
            'antipulga' => 'id_antipulgas',
            'cirugia' => 'id_cirugia'
        ];
        
        return $claves[$tipoHistoria] ?? 'id_consulta';
    }
}