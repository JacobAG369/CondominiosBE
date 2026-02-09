<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
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

  // TEST rápido para generar notificaciones desde UI
  Route::post('/notifications/test', [NotificationController::class, 'testCreate']);

  // CATALOG ENDPOINTS
  Route::get('/catalog/departamentos', [CatalogController::class, 'departamentos']);
  Route::get('/catalog/roles', [CatalogController::class, 'roles']);

  // ADMIN ENDPOINTS
  Route::get('/admin/users', [AdminUserController::class, 'index']);
  Route::post('/admin/users', [AdminUserController::class, 'store']);
});
