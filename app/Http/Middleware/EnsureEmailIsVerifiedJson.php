<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Versión JSON del middleware "verified" de Laravel.
 * En lugar de redirigir a /email/verify (comportamiento web),
 * retorna un 403 JSON adecuado para APIs.
 */
class EnsureEmailIsVerifiedJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Tu correo electrónico no ha sido verificado. '
                    . 'Por favor revisa tu bandeja de entrada y haz clic en el enlace de verificación.',
                'email_verified' => false,
            ], 403);
        }

        return $next($request);
    }
}
