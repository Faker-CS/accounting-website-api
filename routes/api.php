<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public login
Route::post('login', [AuthController::class, 'login']);

// Protected routes (JWT)
Route::middleware('auth:api')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Only Comptable can create new users
    Route::post('users', [AuthController::class, 'createUser'])
         ->middleware('role:comptable');
});