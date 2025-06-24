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
use App\Http\Controllers\CommentController;





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
    Route::get('companies', [CompanyController::class, 'index']);
    Route::get('companies/{id}', [CompanyController::class, 'show']);
    Route::get('/companies/{company}/files', [CompanyFileController::class, 'index']);
    Route::post('/companies/{company}/files', [CompanyFileController::class, 'store']);
    Route::delete('/companies/{company}/files', [CompanyFileController::class, 'destroy']);
    Route::post('/companies/{company}/files/send-email', [CompanyFileController::class, 'sendFileByEmail']);

    // List all aide-comptables
    Route::get('/aideComptables', [AideComptableController::class, 'index']);
    Route::delete('/aideComptable/{id}', [AideComptableController::class, 'destroy']);
    Route::post('/aideComptable', [AideComptableController::class, 'store']);
    Route::put('/aideComptable/{id}', [AideComptableController::class, 'update']);

    // chat routes
    Route::get('/contacts/{id}', [ChatController::class, 'getContacts']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/conversations/{id}', [ChatController::class, 'getConversationById']);
    Route::post('/conversations', [ChatController::class, 'createConversation']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::post('/conversations/{id}/mark-as-seen', [ChatController::class, 'markAsSeen']);

    // Group conversation management
    Route::post('/conversations/{id}/add-participant', [ChatController::class, 'addParticipant']);
    Route::post('/conversations/{id}/remove-participant', [ChatController::class, 'removeParticipant']);

    // File upload for chat messages
    Route::post('/messages/with-attachment', [ChatController::class, 'sendMessageWithAttachment']);

    // Read receipts for group conversations
    Route::post('/conversations/{id}/read-receipt', [ChatController::class, 'updateReadReceipt']);
    Route::get('/conversations/{id}/read-receipts', [ChatController::class, 'getReadReceipts']);

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
    Route::middleware(['role:entreprise'])->group(function () {
        Route::get('/services/{id}/documents', [DocumentController::class, 'getDocumentsByService']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::put('/user/profile/matricule', [UserController::class, 'updateProfileMatricule']);
        Route::post('/documents/upload', [DocumentController::class, 'uploadDocument']);
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

    // Comment routes
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    

});

Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
