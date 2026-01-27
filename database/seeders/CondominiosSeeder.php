<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CondominiosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['rol' => 'Residente'],
            ['rol' => 'Administrador'],
        ]);

        $departamentos = [
            ['depa' => 'A-101', 'moroso' => false, 'codigo' => 'A101'],
            ['depa' => 'A-102', 'moroso' => false, 'codigo' => 'A102'],
            ['depa' => 'B-201', 'moroso' => false, 'codigo' => 'B201'],
            ['depa' => 'B-202', 'moroso' => true,  'codigo' => 'B202'],
            ['depa' => 'C-301', 'moroso' => false, 'codigo' => 'C301'],
            ['depa' => 'C-302', 'moroso' => false, 'codigo' => 'C302'],
        ];

        foreach ($departamentos as $depa) {
            DB::table('departamentos')->insert($depa);
        }

        $personas = [
            ['nombre' => 'Lionel', 'apellido_p' => 'Messi', 'apellido_m' => 'Cuccittini', 'celular' => '712345678', 'activo' => true],
            ['nombre' => 'Cristiano', 'apellido_p' => 'Ronaldo', 'apellido_m' => 'dos Santos', 'celular' => '712345679', 'activo' => true],
            ['nombre' => 'Neymar', 'apellido_p' => 'da Silva', 'apellido_m' => 'Santos', 'celular' => '712345680', 'activo' => true],
            ['nombre' => 'Kylian', 'apellido_p' => 'Mbappe', 'apellido_m' => 'Lottin', 'celular' => '712345681', 'activo' => true],
            ['nombre' => 'Kevin', 'apellido_p' => 'De', 'apellido_m' => 'Bruyne', 'celular' => '712345682', 'activo' => true],
            ['nombre' => 'Luka', 'apellido_p' => 'Modric', 'apellido_m' => 'Rudolf', 'celular' => '712345683', 'activo' => true],
        ];

        $personaIds = [];
        foreach ($personas as $persona) {
            $personaIds[] = DB::table('personas')->insertGetId($persona);
        }

        $usuarioIds = [];
        foreach ($personaIds as $index => $personaId) {
            $usuarioIds[] = DB::table('usuarios')->insertGetId([
                'id_persona' => $personaId,
                'pass' => Hash::make('condo123'),
                'admin' => $index === 0,
            ]);
        }

        $departamentoIds = DB::table('departamentos')->pluck('id')->all();

        foreach ($personaIds as $index => $personaId) {
            $depaId = $departamentoIds[$index % count($departamentoIds)];
            DB::table('per_dep')->insert([
                'id_persona' => $personaId,
                'id_depa' => $depaId,
                'id_rol' => 1,
                'residente' => true,
                'codigo' => 'RES-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]);
        }

        DB::table('mensajes')->insert([
            [
                'remitente' => $usuarioIds[0],
                'destinatario' => null,
                'id_depaA' => $departamentoIds[0],
                'id_depaB' => null,
                'mensaje' => 'Hola vecinos, el chat ya esta listo.',
                'fecha' => now()->subMinutes(10),
            ],
            [
                'remitente' => $usuarioIds[1],
                'destinatario' => null,
                'id_depaA' => $departamentoIds[1],
                'id_depaB' => null,
                'mensaje' => 'Perfecto, hagamos una prueba en tiempo real.',
                'fecha' => now()->subMinutes(7),
            ],
        ]);
    }
}
