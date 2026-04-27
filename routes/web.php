<?php

use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Sin vistas - todo manejado por React/Frontend

// ============ RUTA DE LOGIN PARA REDIRECCIÓN (NO AFECTA FRONTEND) ============
// Esta ruta solo existe para satisfacer el middleware Authenticate
// No se usa realmente porque el frontend maneja el login
Route::get('/login', function () {
    return response()->json([
        'message' => 'No autenticado. Por favor inicie sesión.'
    ], 401);
})->name('login');