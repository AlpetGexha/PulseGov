<?php

use App\Models\AIAnalysis;
use App\Models\AIModelMetrics;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

test('ai performance monitoring command collects metrics', function () {
    // Create some feedback with AI analysis
    $user = User::factory()->create();

    // Get a date to work with
    $today = now();

    // Create feedback dated today
    $feedback = Feedback::factory()
        ->count(10)
        ->create([
            'user_id' => $user->id,
            'created_at' => $today->copy()->subHours(2),
        ]);

    // Create AI analysis for the feedback
    foreach ($feedback as $item) {
        AIAnalysis::factory()->create([
            'feedback_id' => $item->id,
            'created_at' => $today->copy()->subHours(1), // 1 hour after feedback creation
        ]);
    }

    // Create some older feedback
    Feedback::factory()
        ->count(5)
        ->create([
            'user_id' => $user->id,
            'created_at' => $today->copy()->subDays(2),
        ]);

    // Run the command
    $output = Artisan::call('ai:monitor-performance');

    // Verify the command executed successfully
    expect($output)->toBe(0);

    // Check that the metrics were recorded
    $metrics = AIModelMetrics::latest()->first();
    expect($metrics)->toBeInstanceOf(AIModelMetrics::class)
        ->and($metrics->date)->toBe($today->format('Y-m-d'))
        ->and($metrics->analyses_count)->toBe(10)
        ->and($metrics->avg_processing_time)->toBeGreaterThan(0);
});
