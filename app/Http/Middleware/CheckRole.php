<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga el rol requerido
 * según la tabla per_dep (id_rol).
 *
 * Uso en rutas:
 *   ->middleware('role:1')   // solo Residentes
 *   ->middleware('role:2')   // solo Administradores (o admin=true en usuarios)
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, int $roleId): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // Los administradores globales (admin=true) siempre pasan si el rol requerido es 2.
        if ((int) $roleId === 2 && $user->admin) {
            return $next($request);
        }

        // Buscar el rol del usuario en per_dep.
        $perDep = DB::table('per_dep')
            ->where('id_persona', $user->id_persona)
            ->first();

        if (!$perDep || (int) $perDep->id_rol !== (int) $roleId) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        return $next($request);
    }
}
