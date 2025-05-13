<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AideComptableController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;

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
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('companies/{id}', [CompanyController::class, 'show']);
    
    // List all aide-comptables
    Route::get('/aideComptables', [AideComptableController::class, 'index']);
    Route::delete('/aideComptable/{id}', [AideComptableController::class, 'destroy']);
    Route::post('/aideComptable', [AideComptableController::class, 'store']);
    Route::put('/aideComptable/{id}', [AideComptableController::class, 'update']);

    // chat routes
    Route::get('conversations', [ChatController::class, 'getConversations']);
    Route::post('new-conversation', [ChatController::class,'createConversation']);
    Route::get('conversations/{id}', [ChatController::class, 'getConversationById']);
    Route::post('send-messages', [ChatController::class, 'sendMessage']);
    Route::post('conversations/{id}/seen', [ChatController::class,'MarkAsSeen']);
    Route::get('contacts/{id}', [ChatController::class,'getContacts']);

    // User profile routes
    Route::get('/profile', [UserController::class, 'edit']);
    Route::put('/profile', [UserController::class, 'update']);
    
});