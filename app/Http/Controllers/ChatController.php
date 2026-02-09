<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\NotificationCreated;
use App\Models\Message;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    // GET /api/chat/messages
    public function index()
    {
        return Message::orderByDesc('id')->limit(100)->get()->reverse()->values();
    }

    // POST /api/chat/messages
    public function store(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);


        $fromDepaId = $request->user()->id_depa ?? null;



        if (!$fromDepaId) {
            return response()->json(['message' => 'No se pudo determinar el departamento del usuario.'], 422);
        }

        $msg = Message::create([
            'from_depa_id' => $fromDepaId,
            'user_id' => $request->user()->id ?? null,
            'content' => $request->input('content'),
        ]);

        broadcast(new MessageSent($msg));

        // Crear notificaciones para todos los departamentos (excepto el emisor)
        $allDepas = DB::table('per_dep')->distinct()->pluck('id_depa');
        foreach ($allDepas as $depa) {
            if ($depa != $fromDepaId) {
                $notification = AppNotification::create([
                    'depa_id' => $depa,
                    'type' => 'message',
                    'title' => "Nuevo mensaje de Depa {$fromDepaId}",
                    'body' => Str::limit($request->input('content'), 100),
                ]);
                broadcast(new NotificationCreated($notification));
            }
        }

        return $msg;
    }
}
