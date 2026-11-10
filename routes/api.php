<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;

Route::get('/hirz', function () {
    return response()->json([
        'app' => 'حِرْز',
        'message' => 'مرحبًا بك في حِرْز',
        'status' => 'success',
    ], 200, [], JSON_UNESCAPED_UNICODE);
});


// =========================
// Authentication
// =========================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// =========================
// Protected Routes
// =========================

Route::middleware('auth:sanctum')->group(function () {

    // User
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);


    // =========================
    // Conversations
    // =========================

    Route::post(
        '/conversations',
        [ConversationController::class, 'store']
    );

    Route::get(
        '/conversations',
        [ConversationController::class, 'index']
    );

    Route::get(
        '/conversations/archived/list',
        [ConversationController::class, 'archived']
    );

    Route::get(
        '/conversations/{conversation}',
        [ConversationController::class, 'show']
    );

    Route::post(
        '/conversations/{conversation}/messages',
        [ConversationController::class, 'sendMessage']
    );

    Route::patch(
        '/conversations/{conversation}/archive',
        [ConversationController::class, 'archive']
    );

    Route::patch(
        '/conversations/{conversation}/restore',
        [ConversationController::class, 'restore']
    );

    Route::delete(
        '/conversations/{conversation}',
        [ConversationController::class, 'destroy']
    );

});
