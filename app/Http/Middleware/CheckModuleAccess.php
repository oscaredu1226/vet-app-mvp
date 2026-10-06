<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module  Nombre del módulo a verificar (caja, ventas, egresos)
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = auth()->user();

        // Los administradores siempre tienen acceso
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Verificar permiso específico del módulo
        $permission = 'puede_acceder_' . $module;

        if (!$user || !$user->$permission) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
