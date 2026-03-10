<?php

namespace App\Http\Controllers;

use App\Mail\SendResetCodeEmail;
use App\Models\PasswordResetCode;
use App\Models\Persona;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            'device_id' => 'required|string',
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

        // Un token por dispositivo: revocar el token previo de este device_id antes de crear uno nuevo
        $user->tokens()->where('name', $request->device_id)->delete();

        $token = $user->createToken($request->device_id)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('persona'),
        ]);
    }

    /**
     * Cambia la contraseña y revoca todos los tokens (logout global).
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->pass)) {
            return response()->json(['message' => 'La contraseña actual es incorrecta.'], 422);
        }

        $user->pass = Hash::make($request->new_password);
        $user->save();

        // Logout global: revocar todos los tokens de este usuario en todos sus dispositivos
        $user->tokens()->delete();

        return response()->json(['message' => 'Contraseña actualizada. Se ha cerrado sesión en todos los dispositivos.']);
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

    // ─────────────────────────────────────────────────────────────────────────
    // Password Reset via 6-digit code
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera y envía un código de 6 dígitos al correo del usuario.
     * Elimina cualquier código previo para ese email antes de crear uno nuevo.
     */
    public function sendCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ]);

        // Generar código numérico de 6 dígitos, con padding por si acaso
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Borrar códigos previos y guardar el nuevo
        PasswordResetCode::where('email', $request->email)->delete();

        PasswordResetCode::create([
            'email' => $request->email,
            'code' => $code,
            'created_at' => Carbon::now(),
        ]);

        Mail::to($request->email)->send(new SendResetCodeEmail($code));

        return response()->json([
            'message' => 'Código de verificación enviado al correo electrónico.',
            'data' => null,
        ]);
    }

    /**
     * Verifica que el código sea válido y no haya expirado (15 minutos).
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $record = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'El código es inválido.',
                'data' => null,
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json([
                'message' => 'El código ha expirado. Por favor solicita uno nuevo.',
                'data' => null,
            ], 422);
        }

        return response()->json([
            'message' => 'Código válido.',
            'data' => null,
        ]);
    }

    /**
     * Restablece la contraseña del usuario después de validar el código.
     * Elimina el código de la base de datos tras usarlo.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'El código es inválido.',
                'data' => null,
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            return response()->json([
                'message' => 'El código ha expirado. Por favor solicita uno nuevo.',
                'data' => null,
            ], 422);
        }

        // Actualizar la contraseña del usuario (columna 'pass')
        $user = User::where('email', $request->email)->firstOrFail();
        $user->pass = Hash::make($request->password);
        $user->save();

        // Invalidar el código para que no pueda reutilizarse
        $record->delete();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
            'data' => null,
        ]);
    }
}
