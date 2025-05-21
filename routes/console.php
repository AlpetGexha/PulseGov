<?php

declare(strict_types=1);

use App\Jobs\ProcessFeedbackAIAnalysis;
use App\Models\Feedback;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// // Schedule tasks
// Schedule::command('feedback:analyze-all')->hourly()
//     ->description('Process unanalyzed feedback with AI')
//     ->runInBackground();

// // Monitor AI performance daily
// Schedule::command('ai:monitor-performance')->dailyAt('23:45')
//     ->description('Track and record AI analysis performance metrics');

// // Process any feedback that hasn't been analyzed after 5 minutes of creation
// Schedule::call(function () {
//     Feedback::whereDoesntHave('aIAnalysis')
//         ->where('created_at', '<=', now()->subMinutes(5))
//         ->chunk(10, function ($feedbackItems) {
//             foreach ($feedbackItems as $feedback) {
//                 ProcessFeedbackAIAnalysis::dispatch($feedback);
//             }
//         });
// })->everyTenMinutes()
//     ->description('Analyze feedback that was missed')
//     ->runInBackground();
