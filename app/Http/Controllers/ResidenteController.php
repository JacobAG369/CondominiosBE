<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\PerDep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ResidenteController extends Controller
{
    public function index()
    {
        // Lista de personas que tengan per_dep.residente = true
        $residentes = Persona::query()
            ->whereExists(function ($q) {
                $q->selectRaw(1)
                  ->from('per_dep')
                  ->whereColumn('per_dep.id_persona', 'personas.id')
                  ->where('per_dep.residente', true);
            })
            ->get();

        return response()->json($residentes);
    }

    public function show(Persona $persona)
    {
        $perDep = PerDep::where('id_persona', $persona->id)->first();

        return response()->json([
            'persona' => $persona,
            'per_dep' => $perDep,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:255'],
            'apellido_p' => ['required','string','max:255'],
            'apellido_m' => ['nullable','string','max:255'],
            'celular' => ['required','string','max:20', Rule::unique('personas','celular')],
            'activo' => ['sometimes','boolean'],

            'id_depa' => ['required','integer', 'exists:departamentos,id'],
            'id_rol' => ['required','integer', 'exists:roles,id'],
            'residente' => ['required','boolean'],
            'codigo' => ['nullable','string','max:50'],
        ]);

        $persona = DB::transaction(function () use ($data) {
            $persona = Persona::create([
                'nombre' => $data['nombre'],
                'apellido_p' => $data['apellido_p'],
                'apellido_m' => $data['apellido_m'] ?? null,
                'celular' => $data['celular'],
                'activo' => $data['activo'] ?? true,
            ]);

            PerDep::create([
                'id_persona' => $persona->id,
                'id_depa' => $data['id_depa'],
                'id_rol' => $data['id_rol'],
                'residente' => $data['residente'],
                'codigo' => $data['codigo'] ?? null,
            ]);

            return $persona;
        });

        return response()->json($persona, 201);
    }

    public function update(Request $request, Persona $persona)
    {
        $data = $request->validate([
            'nombre' => ['sometimes','string','max:255'],
            'apellido_p' => ['sometimes','string','max:255'],
            'apellido_m' => ['nullable','string','max:255'],
            'celular' => ['sometimes','string','max:20', Rule::unique('personas','celular')->ignore($persona->id)],
            'activo' => ['sometimes','boolean'],

            'id_depa' => ['sometimes','integer', 'exists:departamentos,id'],
            'id_rol' => ['sometimes','integer', 'exists:roles,id'],
            'residente' => ['sometimes','boolean'],
            'codigo' => ['nullable','string','max:50'],
        ]);

        DB::transaction(function () use ($data, $persona) {
            $persona->update(array_filter([
                'nombre' => $data['nombre'] ?? null,
                'apellido_p' => $data['apellido_p'] ?? null,
                'apellido_m' => array_key_exists('apellido_m', $data) ? $data['apellido_m'] : null,
                'celular' => $data['celular'] ?? null,
                'activo' => $data['activo'] ?? null,
            ], fn($v) => $v !== null));

            $perDep = PerDep::where('id_persona', $persona->id)->first();

            if ($perDep) {
                $perDep->update(array_filter([
                    'id_depa' => $data['id_depa'] ?? null,
                    'id_rol' => $data['id_rol'] ?? null,
                    'residente' => $data['residente'] ?? null,
                    'codigo' => array_key_exists('codigo', $data) ? $data['codigo'] : null,
                ], fn($v) => $v !== null));
            }
        });

        return response()->json(['message' => 'Actualizado correctamente']);
    }

    public function destroy(Persona $persona)
    {
        DB::transaction(function () use ($persona) {
            PerDep::where('id_persona', $persona->id)->delete();
            $persona->delete();
        });

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
