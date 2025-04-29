<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\ActivityController;

// Public login
Route::post('login', [AuthController::class, 'login']);

// Protected routes (JWT)
Route::middleware('auth:api')->group(function () {
    // Authenticated user routes
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Only Comptable can create new users
    // Company management routes
    Route::apiResource('companies', CompanyController::class);
        // ->except(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('companies/{company}', [CompanyController::class, 'show']);

    // Industry routes (Comptable only)
    Route::apiResource('industries', IndustryController::class)
        ->middleware('role:comptable');

    // Activity routes (Comptable only)
    Route::apiResource('activities', ActivityController::class)
        ->middleware('role:comptable');

    // User management (Comptable only)
    Route::post('users', [AuthController::class, 'createUser'])
        ->middleware('role:comptable');
});