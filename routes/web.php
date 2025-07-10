<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Analytics Dashboard (admin only)
    Route::get('analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('analytics/generate-ai', [App\Http\Controllers\AnalyticsController::class, 'generateAI'])->name('analytics.generate-ai');
    Route::post('analytics/clear-cache', [App\Http\Controllers\AnalyticsController::class, 'clearCache'])->name('analytics.clear-cache');

    // Chat Routes
    Route::get('chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('chat/conversations', [App\Http\Controllers\ChatController::class, 'createConversation'])->name('chat.conversations.create');
    Route::get('chat/conversations/{conversation}', [App\Http\Controllers\ChatController::class, 'getConversation'])->name('chat.conversations.show');
    Route::post('chat/conversations/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.conversations.messages.store');
    Route::delete('chat/conversations/{conversation}', [App\Http\Controllers\ChatController::class, 'deleteConversation'])->name('chat.conversations.destroy');
    Route::get('chat/history', [App\Http\Controllers\ChatController::class, 'getConversationHistory'])->name('chat.history');
});

// Feedback Forum Routes
Route::get('/feedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
Route::get('/feedback/{feedback}', [App\Http\Controllers\FeedbackController::class, 'show'])->name('feedback.show');

// Protected Feedback Routes
Route::middleware(['auth'])->group(function () {
    // Feedback CRUD
    Route::post('/feedback', [App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
    Route::put('/feedback/{feedback}', [App\Http\Controllers\FeedbackController::class, 'update'])->name('feedback.update');

    // Comments
    Route::post('/feedback/{feedback}/comments', [App\Http\Controllers\FeedbackCommentController::class, 'store'])->name('feedback.comments.store');
    Route::put('/feedback/comments/{comment}', [App\Http\Controllers\FeedbackCommentController::class, 'update'])->name('feedback.comments.update');
    Route::delete('/feedback/comments/{comment}', [App\Http\Controllers\FeedbackCommentController::class, 'destroy'])->name('feedback.comments.destroy');
    Route::post('/feedback/comments/{comment}/toggle-pin', [App\Http\Controllers\FeedbackCommentController::class, 'togglePin'])->name('feedback.comments.toggle-pin');

    // Votes
    Route::post('/feedback/vote', [App\Http\Controllers\FeedbackVoteController::class, 'vote'])->name('feedback.vote');
});

// AJAX Endpoints
Route::get('/feedback/{feedback}/votes', [App\Http\Controllers\FeedbackVoteController::class, 'getVoteCounts'])->name('feedback.votes.count');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
