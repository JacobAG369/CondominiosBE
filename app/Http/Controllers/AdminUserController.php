<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Persona;
use App\Models\PerDep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    // Middleware check
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()->admin) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = DB::table('usuarios')
            ->join('personas', 'usuarios.id_persona', '=', 'personas.id')
            ->leftJoin('per_dep', 'personas.id', '=', 'per_dep.id_persona')
            ->leftJoin('departamentos', 'per_dep.id_depa', '=', 'departamentos.id')
            ->leftJoin('roles', 'per_dep.id_rol', '=', 'roles.id')
            ->select(
                'usuarios.id as user_id',
                'personas.id as persona_id',
                'personas.nombre',
                'personas.apellido_p',
                'personas.apellido_m',
                'personas.celular',
                'usuarios.admin',
                'departamentos.nombre as departamento',
                'departamentos.id as id_depa',
                'roles.nombre as rol',
                'roles.id as id_rol',
                'per_dep.residente',
                'per_dep.codigo'
            )
            ->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_p' => 'required|string|max:255',
            'apellido_m' => 'nullable|string|max:255',
            'celular' => 'required|string|max:20|unique:personas,celular',
            'password' => 'required|string|min:8',
            'id_depa' => 'required|integer|exists:departamentos,id',
            'id_rol' => 'required|integer|exists:roles,id',
            'residente' => 'required|boolean',
            'codigo' => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre es requerido',
            'apellido_p.required' => 'El apellido paterno es requerido',
            'celular.required' => 'El celular es requerido',
            'celular.unique' => 'El celular ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'id_depa.required' => 'El departamento es requerido',
            'id_depa.exists' => 'El departamento seleccionado no existe',
            'id_rol.required' => 'El rol es requerido',
            'id_rol.exists' => 'El rol seleccionado no existe',
        ]);

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
            'admin' => $request->admin ?? false,
        ]);

        $codigo = $request->codigo ?? strtoupper(Str::random(8));

        PerDep::create([
            'id_persona' => $persona->id,
            'id_depa' => $request->id_depa,
            'id_rol' => $request->id_rol,
            'residente' => $request->residente,
            'codigo' => $codigo,
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user->load('persona'),
        ], 201);
    }
}
