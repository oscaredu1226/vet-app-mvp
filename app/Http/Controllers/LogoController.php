<?php

namespace App\Http\Controllers;

use App\Models\Logo; // Asegúrate de tener el modelo 'Logo'
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogoController extends Controller
{
    /**
     * Actualiza el logo de la aplicación.
     * Reemplaza 'modules/cambiar_logo.php'
     * Solo administradores pueden cambiar el logo
     */
    public function update(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,gif,webp|max:2048', // 2MB Max
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $file = $request->file('logo');
            $image_data = file_get_contents($file->getRealPath());
            $base64_image = base64_encode($image_data);
            $mime_type = $file->getMimeType();

            // Buscar el logo existente (ID 1)
            $logo = Logo::find(1);
            
            // Si existe un logo anterior, no es necesario eliminarlo físicamente
            // ya que se almacena en base64 en la base de datos
            // Simplemente lo reemplazamos
            if (!$logo) {
                $logo = new Logo();
                $logo->id = 1;
            }
            
            $logo->imagen = $base64_image;
            $logo->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Logo actualizado correctamente',
                'image_data' => "data:{$mime_type};base64,{$base64_image}"
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina (resetea) el logo.
     * Reemplaza 'modules/eliminar_logo.php'
     * Solo administradores pueden eliminar el logo
     */
    public function destroy(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        try {
            $logo = Logo::find(1);
            if ($logo) {
                // Eliminar la imagen anterior de la base de datos
                $logo->imagen = null;
                $logo->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logo eliminado correctamente',
                'default_image' => asset('images/usuario/user.svg') // Ruta al logo por defecto
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}