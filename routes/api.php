<?php

use App\Http\Controllers\MensajeController;
use Illuminate\Support\Facades\Route;

Route::get('/mensajes', [MensajeController::class, 'index']);
Route::post('/mensajes', [MensajeController::class, 'store']);
