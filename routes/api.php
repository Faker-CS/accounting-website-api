<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AideComptableController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\DemandeAssignController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CompanyFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\SubtaskTemplateController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EmployeeController;





// Public login
Route::post('login', [AuthController::class, 'login']);

// Broadcasting auth (special handling)
Route::post('/broadcasting/auth', function (Request $request) {
    
    if (!auth()->check()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    return Broadcast::auth($request);
})->middleware('auth:api');

// Protected routes (JWT)
Route::middleware('auth:api')->group(function () {
    // Authenticated user routes
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Only Comptable can create new users
    // Company management routes
    Route::apiResource('companies', CompanyController::class);
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/companies/{id}', [CompanyController::class, 'show']);
    Route::get('/companies/{company}/files', [CompanyFileController::class, 'index']);
    Route::post('/companies/{company}/files', [CompanyFileController::class, 'store']);
    Route::delete('/companies/{company}/files', [CompanyFileController::class, 'destroy']);
    Route::post('/companies/{company}/files/send-email', [CompanyFileController::class, 'sendFileByEmail']);

    // List all aide-comptables
    Route::get('/aideComptables', [AideComptableController::class, 'index']);
    Route::get('/aideComptable/{id}', action: [AideComptableController::class, 'show']);
    Route::delete('/aideComptable/{id}', [AideComptableController::class, 'destroy']);
    Route::post('/aideComptable', [AideComptableController::class, 'store']);
    Route::put('/aideComptable/{id}', [AideComptableController::class, 'update']);

    // chat routes
    Route::get('/chat/contacts/{id}', [ChatController::class, 'getContacts']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/conversations/{id}', [ChatController::class, 'getConversationById']);
    Route::post('/chat/send-messages', [ChatController::class, 'sendMessage']);
    Route::post('/chat/create-conversation', [ChatController::class, 'createConversation']);
    Route::post('/chat/conversations/{id}/mark-as-seen', [ChatController::class, 'markAsSeen']);

    // File upload for chat messages
    Route::post('/messages/with-attachment', [ChatController::class, 'sendMessageWithAttachment']);

    // User profile routes
    Route::get('/profile', [UserController::class, 'edit']);
    Route::put('/profile', [UserController::class, 'update']);
    Route::post('/profile', [UserController::class, 'update']);

    // manage documents
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    Route::get('/documents/{id}', [DocumentController::class, 'getDocument']);
    Route::get('/documents/download/{id}', [DocumentController::class, 'download']);

    // Get Status
    Route::get('/status/{serviceId}', [DocumentController::class, 'getStatus']);
    Route::get('/statusId/{id}', [DocumentController::class, 'getStatusById']);

    // Submit Form
    Route::post('/form/{serviceId}', [FormController::class, 'submitForm']);
    // assign form to aide-comptable
    Route::post('/demandes/assign/{demandId}', [DemandeAssignController::class, 'assignHelperToDemande']);



    // Forms routes
    Route::get('/forms', [FormController::class, 'getForms']);
    Route::delete('/forms/{id}', [FormController::class, 'destroy']);
    Route::patch('/forms/{id}', [FormController::class, 'update']);
    Route::post('/forms/{id}/accept', [FormController::class, 'acceptDemand']);
    Route::get('/forms/{id}', [FormController::class, 'get']);
    Route::delete('/forms/document/{id}', [FormController::class, 'documentDelete']);
    Route::get('/statistics', [FormController::class, 'getStatistics']);

    // service routes
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::get('/services/comptable', [ServiceController::class, 'getComptableServices']);
    
    // Admin only service management
    Route::middleware(['role:comptable'])->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
        Route::post('/companies/{companyId}/assign-default-services', [ServiceController::class, 'assignDefaultServicesToCompany']);
    });

    // Company service management
    // Company service management - Allow both comptable and entreprise
    Route::middleware(['role:comptable|entreprise'])->group(function () {
        Route::get('/companies/{companyId}/services', [ServiceController::class, 'getCompanyServices']);
        Route::get('/companies/{companyId}/services-with-status', [ServiceController::class, 'getServicesWithCompanyStatus']);
    });

    Route::middleware(['role:entreprise'])->group(function () {
        Route::get('/services/{id}/documents', [DocumentController::class, 'getDocumentsByService']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::put('/user/profile/matricule', [UserController::class, 'updateProfileMatricule']);
        Route::post('/documents/upload', [DocumentController::class, 'uploadDocument']);
        
        // Company service modification routes (only for company owners)
        Route::post('/companies/{companyId}/services', [ServiceController::class, 'assignServiceToCompany']);
        Route::put('/companies/{companyId}/services/{serviceId}', [ServiceController::class, 'updateCompanyService']);
        Route::delete('/companies/{companyId}/services/{serviceId}', [ServiceController::class, 'removeServiceFromCompany']);
    });

    Route::get('/user/documents/{serviceId}/{id}', [DocumentController::class, 'getUserDocumentsByService']);

    // notifications
    Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);
    Route::patch('/notifications/read', [NotificationController::class, 'allRead']);
    Route::patch('/notifications/read/{id}', [NotificationController::class, 'read']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // calendar
    Route::apiResource('calendar', controller: EventController::class)->only(['index', 'store', 'update', 'destroy']);
    // Task routes
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Subtask routes
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store']);
    Route::put('/subtasks/{id}', [SubtaskController::class, 'update']);
    Route::delete('/subtasks/{id}', [SubtaskController::class, 'destroy']);

    // Subtask template routes
    Route::apiResource('subtask-templates', SubtaskTemplateController::class);
    Route::post('/subtask-templates/reorder', [SubtaskTemplateController::class, 'reorder']);

    // Comment routes
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Employee routes
    Route::apiResource('employees', EmployeeController::class);
    Route::get('/companies/{company}/employees', [EmployeeController::class, 'getByCompany']);


    

});

Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
