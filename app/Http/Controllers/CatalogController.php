<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function departamentos()
    {
        $departamentos = DB::table('departamentos')->select('id', 'nombre')->orderBy('nombre')->get();
        return response()->json($departamentos);
    }

    public function roles()
    {
        $roles = DB::table('roles')->select('id', 'nombre')->orderBy('nombre')->get();
        return response()->json($roles);
    }
}
