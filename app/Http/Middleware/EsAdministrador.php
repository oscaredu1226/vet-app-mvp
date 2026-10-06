<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EsAdministrador
{
    /**
     * Maneja una solicitud entrante.
     * Solo permite el acceso si el usuario autenticado es administrador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar que el usuario está autenticado y es administrador
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Acceso denegado. Solo administradores pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
