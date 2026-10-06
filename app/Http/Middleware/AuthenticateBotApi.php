<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateBotApi
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('bot_api.token');
        if (!is_string($expected) || $expected === '') {
            return response()->json(['message' => 'La integración del bot no está configurada.'], 503);
        }
        $provided = $request->bearerToken();
        if (!is_string($provided) || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }
        return $next($request);
    }
}
