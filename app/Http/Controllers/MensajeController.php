<?php

namespace App\Http\Controllers;

use App\Events\MensajeCreado;
use App\Models\Mensaje;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    public function index(Request $request)
    {
        $query = Mensaje::query()->orderBy('fecha');

        if ($request->filled('depa')) {
            $depa = (int) $request->query('depa');
            $query->where(function ($builder) use ($depa): void {
                $builder->where('id_depaA', $depa)
                    ->orWhere('id_depaB', $depa);
            });
        }

        $mensajes = $query->limit(200)->get();

        return response()->json($mensajes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'remitente' => ['required', 'integer', 'exists:usuarios,id'],
            'destinatario' => ['nullable', 'integer', 'exists:usuarios,id'],
            'id_depaA' => ['required', 'integer', 'exists:departamentos,id'],
            'id_depaB' => ['nullable', 'integer', 'exists:departamentos,id'],
            'mensaje' => ['required', 'string', 'max:1000'],
        ]);

        $mensaje = Mensaje::create([
            'remitente' => $validated['remitente'],
            'destinatario' => $validated['destinatario'] ?? null,
            'id_depaA' => $validated['id_depaA'],
            'id_depaB' => $validated['id_depaB'] ?? null,
            'mensaje' => $validated['mensaje'],
            'fecha' => now(),
        ]);

        broadcast(new MensajeCreado($mensaje));

        return response()->json($mensaje, 201);
    }
}
