<?php

declare(strict_types=1);

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\API\FeedbackCommentController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\FeedbackVoteController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\JobProgressController;
use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});


// Protected Feedback Routes
Route::middleware(['auth'])->group(function () {
    // Analytics Dashboard (admin only)
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('analytics/generate-ai', [AnalyticsController::class, 'generateAI'])->name('analytics.generate-ai');
    Route::post('analytics/clear-cache', [AnalyticsController::class, 'clearCache'])->name('analytics.clear-cache');

    // Map Route
    Route::get('map', MapController::class)->name('map.index');

    // Chat Routes
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat/conversations', [ChatController::class, 'createConversation'])->name('chat.conversations.create');
    Route::get('chat/conversations/{conversation}', [ChatController::class, 'getConversation'])->name('chat.conversations.show');
    Route::post('chat/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.conversations.messages.store');
    Route::delete('chat/conversations/{conversation}', [ChatController::class, 'deleteConversation'])->name('chat.conversations.destroy');
    Route::get('chat/history', [ChatController::class, 'getConversationHistory'])->name('chat.history');

    // Feedback Forum Routes
    Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::put('feedback/{feedback}', [FeedbackController::class, 'update'])->name('feedback.update');

    // Comments
    Route::post('feedback/{feedback}/comments', [FeedbackCommentController::class, 'store'])->name('feedback.comments.store');
    Route::put('feedback/comments/{comment}', [FeedbackCommentController::class, 'update'])->name('feedback.comments.update');
    Route::delete('feedback/comments/{comment}', [FeedbackCommentController::class, 'destroy'])->name('feedback.comments.destroy');
    Route::post('feedback/comments/{comment}/toggle-pin', [FeedbackCommentController::class, 'togglePin'])->name('feedback.comments.toggle-pin');

    // Votes
    Route::post('feedback/vote', [FeedbackVoteController::class, 'vote'])->name('feedback.vote');

    // Job Progress Route
    Route::get('job-progress/{key}', [JobProgressController::class, 'getProgress'])->name('job.progress');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
