<?php

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

// Feedback API routes - protected by authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('feedback', FeedbackController::class);

    // Custom route for analyzing feedback
    Route::post('feedback/{id}/analyze', [FeedbackController::class, 'analyze'])->name('feedback.analyze');

    // Dashboard routes
    Route::get('dashboard/feedback-stats', [\App\Http\Controllers\API\DashboardController::class, 'getFeedbackStats']);
    Route::get('dashboard/recent-activity', [\App\Http\Controllers\API\DashboardController::class, 'getRecentActivity']);
});
