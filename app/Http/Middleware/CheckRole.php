<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class CheckRole
{
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $authenticatedUser = $request->user('sanctum');

        if (! $authenticatedUser instanceof Authenticatable) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $usuario = $this->resolveUsuario($authenticatedUser);

        if ($usuario === null || ! $this->matchesRequiredRole($usuario, $requiredRole)) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    private function resolveUsuario(Authenticatable $authenticatedUser): ?Usuario
    {
        if ($authenticatedUser instanceof Usuario) {
            return $authenticatedUser;
        }

        $identifier = $authenticatedUser->getAuthIdentifier();

        if ($identifier === null) {
            return null;
        }

        return Usuario::query()->find($identifier);
    }

    private function matchesRequiredRole(Usuario $usuario, string $requiredRole): bool
    {
        $requiredRole = trim($requiredRole);

        if ($requiredRole === '') {
            return false;
        }

        return $this->matchesTipoUsuarioRole($usuario, $requiredRole)
            || $this->matchesLegacyRole($usuario, $requiredRole);
    }

    private function matchesTipoUsuarioRole(Usuario $usuario, string $requiredRole): bool
    {
        $userRoleId = $usuario->getAttribute('id_tipo_usuario');

        if ($userRoleId === null) {
            return false;
        }

        if ($this->isNumericRole($requiredRole)) {
            return (int) $userRoleId === (int) $requiredRole;
        }

        $roleName = $this->resolveCatalogRoleName('cat_tipo_usuario', (int) $userRoleId);

        return $roleName !== null && $this->sameRoleName($roleName, $requiredRole);
    }

    private function matchesLegacyRole(Usuario $usuario, string $requiredRole): bool
    {
        $idPersona = $usuario->getAttribute('id_persona');

        if ($idPersona === null || ! $this->legacyRoleTablesExist()) {
            return false;
        }

        // Compatibility fallback for the current project schema.
        if ($this->isAdministratorRequest($requiredRole) && (bool) $usuario->getAttribute('admin')) {
            return true;
        }

        $legacyRole = DB::table('per_dep')
            ->join('roles', 'roles.id', '=', 'per_dep.id_rol')
            ->where('per_dep.id_persona', (int) $idPersona)
            ->select('per_dep.id_rol', 'roles.rol')
            ->first();

        if ($legacyRole === null) {
            return false;
        }

        if ($this->isNumericRole($requiredRole)) {
            return (int) $legacyRole->id_rol === (int) $requiredRole;
        }

        return $this->sameRoleName((string) $legacyRole->rol, $requiredRole);
    }

    private function resolveCatalogRoleName(string $table, int $roleId): ?string
    {
        if (! $this->hasTable($table)) {
            return null;
        }

        foreach (['nombre', 'tipo_usuario', 'descripcion', 'rol'] as $column) {
            if (! $this->hasColumn($table, $column)) {
                continue;
            }

            $roleName = DB::table($table)
                ->where('id', $roleId)
                ->value($column);

            if (is_string($roleName) && $roleName !== '') {
                return $roleName;
            }
        }

        return null;
    }

    private function legacyRoleTablesExist(): bool
    {
        return $this->hasTable('per_dep') && $this->hasTable('roles');
    }

    private function hasTable(string $table): bool
    {
        static $cache = [];

        return $cache[$table] ??= Schema::hasTable($table);
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $cacheKey = $table . '.' . $column;

        return $cache[$cacheKey] ??= Schema::hasColumn($table, $column);
    }

    private function isAdministratorRequest(string $requiredRole): bool
    {
        if ($this->isNumericRole($requiredRole)) {
            return (int) $requiredRole === 2;
        }

        return $this->sameRoleName($requiredRole, 'Administrador');
    }

    private function isNumericRole(string $requiredRole): bool
    {
        return ctype_digit($requiredRole);
    }

    private function sameRoleName(string $actualRole, string $requiredRole): bool
    {
        return strtolower(trim($actualRole)) === strtolower(trim($requiredRole));
    }

    private function forbiddenResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Acceso no autorizado',
        ], 403);
    }
}
