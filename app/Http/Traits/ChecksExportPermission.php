<?php

namespace App\Http\Traits;

/**
 * Trait para centralizar la verificación de permisos de exportación
 * Elimina código duplicado en múltiples controladores
 */
trait ChecksExportPermission
{
    /**
     * Verifica si el usuario tiene permiso para exportar
     *
     * @param string $permissionField Nombre del campo de permiso (ej: 'puede_exportar_productos')
     * @param string $errorMessage Mensaje de error personalizado
     * @return \Illuminate\Http\RedirectResponse|null Retorna redirect si no tiene permiso, null si sí
     */
    protected function checkExportPermission(string $permissionField, string $errorMessage)
    {
        $user = auth()->user();
        
        // Admin siempre puede exportar
        if ($user->isAdmin()) {
            return null;
        }

        // Verificar permiso específico
        if (!$user->$permissionField) {
            return redirect()->back()->with('error', $errorMessage);
        }

        return null;
    }
}
