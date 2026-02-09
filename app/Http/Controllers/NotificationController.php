<?php

namespace App\Http\Controllers;

use App\Events\NotificationCreated;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request)
    {
        $depaId = $request->user()->id_depa ?? null;
        if (!$depaId)
            return response()->json(['message' => 'Sin depa'], 422);

        return AppNotification::where('depa_id', $depaId)
            ->orderByDesc('id')
            ->limit(100)
            ->get();
    }

    // GET /api/notifications/unread-count
    public function unreadCount(Request $request)
    {
        $depaId = $request->user()->id_depa ?? null;
        if (!$depaId)
            return response()->json(['message' => 'Sin depa'], 422);

        $count = AppNotification::where('depa_id', $depaId)->whereNull('read_at')->count();
        return ['count' => $count];
    }

    // POST /api/notifications/{id}/read
    public function markRead(Request $request, $id)
    {
        $depaId = $request->user()->id_depa ?? null;
        if (!$depaId)
            return response()->json(['message' => 'Sin depa'], 422);

        $n = AppNotification::where('depa_id', $depaId)->findOrFail($id);
        $n->update(['read_at' => now()]);
        return $n;
    }

    //pruebasss
    public function testCreate(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
        ]);

        $depaId = $request->user()->id_depa ?? null;
        if (!$depaId)
            return response()->json(['message' => 'Sin depa'], 422);

        $n = AppNotification::create([
            'depa_id' => $depaId,
            'type' => $request->type,
            'title' => $request->title,
            'body' => $request->body,
            'data' => $request->data,
        ]);

        broadcast(new NotificationCreated($n))->toOthers();

        return $n;
    }

    // GET /api/notifications/{id}
    public function show(Request $request, $id)
    {
        $depaId = $request->user()->id_depa ?? null;
        if (!$depaId)
            return response()->json(['message' => 'Sin depa'], 422);

        $notification = AppNotification::where('depa_id', $depaId)->findOrFail($id);
        return $notification;
    }
}
