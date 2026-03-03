<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario.
     *
     * Flujo (dentro de una transacción):
     *  1. Crear registro en `personas`.
     *  2. Crear registro en `usuarios` vinculando id_persona.
     *  3. Crear registro en `per_dep` con id_rol = 1 (Residente por defecto).
     *  4. Disparar notificación de verificación de correo.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',   // apellido_p en DB
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
            // Opcionales – mantienen compatibilidad con el flujo anterior
            'celular' => 'nullable|string|max:20|unique:personas,celular',
            'id_depa' => 'nullable|integer|exists:departamentos,id',
        ]);

        $user = DB::transaction(function () use ($request) {
            // 1. Crear persona
            $persona = Persona::create([
                'nombre' => $request->nombre,
                'apellido_p' => $request->apellido,          // mapeado
                'apellido_m' => $request->apellido_m ?? null,
                'celular' => $request->celular ?? null,
                'activo' => true,
            ]);

            // 2. Crear usuario vinculado a la persona
            $user = User::create([
                'id_persona' => $persona->id,
                'email' => $request->email,
                'pass' => Hash::make($request->password),
                'admin' => false,
            ]);

            // 3. Crear relación persona-departamento solo si se proporcionó id_depa
            if ($request->filled('id_depa')) {
                $codigo = strtoupper(Str::random(8));
                DB::table('per_dep')->insert([
                    'id_persona' => $persona->id,
                    'id_depa' => $request->id_depa,
                    'id_rol' => 1, // 1 = Residente
                    'residente' => true,
                    'codigo' => $codigo,
                ]);
            }

            return $user;
        });

        // 4. Enviar email de verificación (fuera de la transacción)
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registro exitoso. Por favor verifica tu correo electrónico '
                . 'para activar tu cuenta.',
            'user' => $user->load('persona'),
        ], 201);
    }

    /**
     * Inicio de sesión.
     * Bloquea el acceso si el correo no ha sido verificado.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->pass)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }

        // Bloquear acceso si el correo no ha sido verificado
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico antes de iniciar sesión. '
                    . 'Revisa tu bandeja de entrada.',
                'email_verified' => false,
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('persona'),
        ]);
    }


    /**
     * Retorna el perfil del usuario autenticado junto con sus datos de departamento y rol.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $idPersona = $user->id_persona;

        $perDep = DB::table('per_dep')
            ->where('id_persona', $idPersona)
            ->first();

        return response()->json([
            'user' => $user,
            'id_persona' => $idPersona,
            'id_depa' => $perDep->id_depa ?? null,
            'id_rol' => $perDep->id_rol ?? null,
            'residente' => $perDep->residente ?? null,
            'admin' => $user->admin ?? false,
            'codigo' => $perDep->codigo ?? null,
        ]);
    }

    /**
     * Cierra sesión revocando el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
