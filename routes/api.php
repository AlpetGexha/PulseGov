<?php

declare(strict_types=1);

use App\Http\Controllers\API\FeedbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routes
Route::post('auth/token', [App\Http\Controllers\API\AuthController::class, 'token'])->name('api.auth.token');
Route::middleware('auth:sanctum')->post('/auth/logout', [App\Http\Controllers\API\AuthController::class, 'logout'])->name('api.auth.logout');

// Public API routes
Route::get('feedback/{feedback}/comments', [App\Http\Controllers\API\FeedbackCommentController::class, 'getComments'])->name('api.feedback.comments');
Route::get('feedback/{feedback}/votes', [App\Http\Controllers\API\FeedbackVoteController::class, 'getVoteCounts'])->name('api.feedback.votes');

// Feedback API routes - protected by authentication
// Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('feedback', FeedbackController::class);

// Custom route for analyzing feedback
Route::post('feedback/{id}/analyze', [FeedbackController::class, 'analyze'])->name('feedback.analyze');

// Feedback comments
Route::post('feedback/comments', [App\Http\Controllers\API\FeedbackCommentController::class, 'store'])->name('api.feedback.comments.store');
Route::put('feedback/comments/{comment}', [App\Http\Controllers\API\FeedbackCommentController::class, 'update'])->name('api.feedback.comments.update');
Route::delete('feedback/comments/{comment}', [App\Http\Controllers\API\FeedbackCommentController::class, 'destroy'])->name('api.feedback.comments.destroy');
Route::post('feedback/comments/{comment}/toggle-pin', [App\Http\Controllers\API\FeedbackCommentController::class, 'togglePin'])->name('api.feedback.comments.toggle-pin');

// Feedback votes
Route::post('feedback/vote', [App\Http\Controllers\API\FeedbackVoteController::class, 'vote'])->name('api.feedback.vote');

// Dashboard routes
Route::get('dashboard/feedback-stats', [App\Http\Controllers\API\DashboardController::class, 'getFeedbackStats']);
Route::get('dashboard/recent-activity', [App\Http\Controllers\API\DashboardController::class, 'getRecentActivity']);
// });
