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
});

// Feedback Forum Routes
Route::get('/feedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
Route::get('/feedback/{feedback}', [App\Http\Controllers\FeedbackController::class, 'show'])->name('feedback.show');

// Protected Feedback Routes
// Route::middleware(['auth'])->group(function () {
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
// });

// AJAX Endpoints
Route::get('/feedback/{feedback}/votes', [App\Http\Controllers\FeedbackVoteController::class, 'getVoteCounts'])->name('feedback.votes.count');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
