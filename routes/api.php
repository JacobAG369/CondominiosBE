<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CatalogController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Rutas PÚBLICAS
// ─────────────────────────────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
// Catálogo de departamentos — público para poder usarlo en el formulario de registro
Route::get('/catalog/departamentos', [CatalogController::class, 'departamentos']);

// Verificación de email — el usuario llega desde el correo SIN token,
// por lo que NO podemos usar auth:sanctum. Verificamos la firma manualmente.
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
  $user = \App\Models\User::findOrFail($id);

  // Comprobar firma válida
  if (!hash_equals((string) $hash, sha1($user->email))) {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/verify-email?error=invalid_link');
  }

  if (!$request->hasValidSignature()) {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/verify-email?error=link_expired');
  }

  if ($user->hasVerifiedEmail()) {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?verified=already');
  }

  $user->markEmailAsVerified();

  return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?verified=1');
})->middleware(['signed', 'throttle:6,1'])
  ->name('verification.verify');


// Reenviar email de verificación
Route::post('/email/resend', function (Request $request) {
  if ($request->user()->hasVerifiedEmail()) {
    return response()->json(['message' => 'Tu correo ya está verificado.'], 200);
  }
  $request->user()->sendEmailVerificationNotification();
  return response()->json(['message' => 'Email de verificación reenviado.']);
})->middleware(['auth:sanctum', 'throttle:6,1'])
  ->name('verification.send');

// ─────────────────────────────────────────────────────────────────────────────
// Rutas PROTEGIDAS — requieren autenticación + email verificado
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
  Route::get('/auth/me', [AuthController::class, 'me']);
  Route::post('/auth/logout', [AuthController::class, 'logout']);

  // CRUD residentes
  Route::get('/residentes', [ResidenteController::class, 'index']);
  Route::post('/residentes', [ResidenteController::class, 'store']);
  Route::get('/residentes/{persona}', [ResidenteController::class, 'show']);
  Route::put('/residentes/{persona}', [ResidenteController::class, 'update']);
  Route::delete('/residentes/{persona}', [ResidenteController::class, 'destroy']);

  // CHAT
  Route::get('/chat/messages', [ChatController::class, 'index']);
  Route::post('/chat/messages', [ChatController::class, 'store']);

  // NOTIFICATIONS
  Route::get('/notifications', [NotificationController::class, 'index']);
  Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
  Route::get('/notifications/{id}', [NotificationController::class, 'show']);
  Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
  Route::post('/notifications/test', [NotificationController::class, 'testCreate']);

  // CATALOG — roles requires auth, departamentos is public (see top of file)
  Route::get('/catalog/roles', [CatalogController::class, 'roles']);

  // ADMIN — solo Administradores (id_rol = 2 o admin = true)
  Route::middleware('role:2')->group(function () {
    Route::get('/admin/stats', [AdminUserController::class, 'stats']);
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users', [AdminUserController::class, 'store']);
  });
});
