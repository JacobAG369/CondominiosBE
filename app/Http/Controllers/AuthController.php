<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Persona;
use App\Models\PerDep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_p' => 'required|string|max:255',
            'apellido_m' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|unique:personas,celular',
            'password' => 'required|string|min:6',

            'id_depa' => 'required|integer',
            'id_rol' => 'required|integer',
            'residente' => 'required|boolean',
        ]);

        // para crear persona
        $persona = Persona::create([
            'nombre' => $request->nombre,
            'apellido_p' => $request->apellido_p,
            'apellido_m' => $request->apellido_m,
            'celular' => $request->celular,
            'activo' => true,
        ]);


        $user = User::create([
            'id_persona' => $persona->id,
            'pass' => Hash::make($request->password),
            'admin' => ($request->id_rol == 2), // si rol 2 = Admin
        ]);


        $codigo = strtoupper(Str::random(8));

        PerDep::create([
            'id_persona' => $persona->id,
            'id_depa' => $request->id_depa,
            'id_rol' => $request->id_rol,
            'residente' => $request->residente,
            'codigo' => $codigo,
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user->load('persona'),
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'celular' => 'required|string',
            'password' => 'required|string',
        ]);

        $persona = \App\Models\Persona::where('celular', $request->celular)->first();

        if (!$persona) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user = \App\Models\User::where('id_persona', $persona->id)->first();

        if (!$user) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }


        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->pass)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Sanctuuuuuuuuuuuuum token te odio
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('persona'),
        ]);
    }


    public function me(Request $request)
    {
        $user = $request->user();

        // 1️⃣ El usuario SIEMPRE tiene id_persona según tu diagrama
        $idPersona = $user->id_persona;

        // 2️⃣ Buscar relación persona-depa
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


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesión cerrada'
        ]);
    }



}

