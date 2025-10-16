<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

Route::middleware('throttle:auth.attempts')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
Route::get('/feedback/top', [FeedbackController::class, 'getTopFeedback']);

Route::middleware('auth:sanctum')->group(function () {
    // Journal
    Route::get('/journals/trashed', [JournalController::class, 'trashed']);    
    Route::get('/journals/stats', [JournalController::class, 'stats']);
    Route::get('/journals', [JournalController::class, 'index']);           
    Route::post('/journals', [JournalController::class, 'store']);          
    Route::get('/journals/{id}', [JournalController::class, 'show']);     
    Route::delete('/journals/{id}', [JournalController::class, 'destroy']); 
    Route::get('/journals/all', [JournalController::class, 'allWithTrashed']);  
    Route::post('/journals/{id}/restore', [JournalController::class, 'restore']);  
    Route::get('/questions', [QuestionController::class, 'getQuestionsByMood']);

    // Feedback
    Route::post('/feedback', [FeedbackController::class, 'store']);

    // Recap 
    Route::post('/journals/recap', [RecapController::class, 'generateRecap']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // LogOut
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/journals', [AdminController::class, 'getAllJournals']);
});



