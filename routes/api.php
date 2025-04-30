<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AideComptableController;

// Public login
Route::post('login', [AuthController::class, 'login']);

// Protected routes (JWT)
Route::middleware('api')->group(function () {
    // Authenticated user routes
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Only Comptable can create new users
    // Company management routes
    Route::apiResource('companies', CompanyController::class);
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('companies/{company}', [CompanyController::class, 'show']);

    // Industry routes (Comptable only)
    Route::apiResource('industries', IndustryController::class)
        ->middleware('role:comptable');

    // Activity routes (Comptable only)
    Route::apiResource('activities', ActivityController::class)
        ->middleware('role:comptable');
    // List all aide-comptables
    Route::get('/aideComptables', [AideComptableController::class, 'index']);
});