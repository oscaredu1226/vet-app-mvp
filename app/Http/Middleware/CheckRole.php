<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  ...$roles  // Roles permitidos (ej. 'admin', 'usuario')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Si el usuario no está autenticado o su rol no está en la lista de roles permitidos
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            // Rechaza la solicitud
            return response()->json(['message' => 'Acceso No Autorizado.'], 403);
        }

        // Permite que la solicitud continúe
        return $next($request);
    }
}
