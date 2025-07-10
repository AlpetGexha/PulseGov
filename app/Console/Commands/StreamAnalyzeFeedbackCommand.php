<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\StreamAnalyzeFeedback;
use App\Models\Feedback;
use Illuminate\Console\Command;

final class StreamAnalyzeFeedbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */ protected $signature = 'analyze:feedback-stream
                            {feedback_id? : The ID of the feedback to analyze}
                            {--real-time : Use real-time streaming of AI thoughts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze feedback with streaming output';

    /**
     * Execute the console command.
     */
    public function handle(StreamAnalyzeFeedback $analyzer)
    {
        $feedbackId = $this->argument('feedback_id');
        $realTime = $this->option('real-time');

        if ($feedbackId) {
            // Process a single feedback item
            $feedback = Feedback::find($feedbackId);

            if (! $feedback) {
                $this->error("Feedback with ID {$feedbackId} not found.");

                return self::FAILURE;
            }

            $this->info("Starting analysis for feedback #{$feedbackId}");
            $this->newLine();

            if ($realTime) {
                $response = $analyzer->analyzeWithRealTimeStream($feedback);
            } else {
                $response = $analyzer->handleStream($feedback);
            }
            $response->send();
        } else {
            // Process unanalyzed feedback items
            $unanalyzed = Feedback::whereDoesntHave('aIAnalysis')->limit(5)->get();

            if ($unanalyzed->isEmpty()) {
                $this->info('No unanalyzed feedback found.');

                return self::SUCCESS;
            }

            $this->info("Found {$unanalyzed->count()} unanalyzed feedback items.");
            $this->newLine();

            $count = 1;
            foreach ($unanalyzed as $feedback) {
                $this->info("Processing item {$count} of {$unanalyzed->count()} (ID: {$feedback->id})");
                $this->newLine();

                if ($realTime) {
                    $response = $analyzer->analyzeWithRealTimeStream($feedback);
                } else {
                    $response = $analyzer->handleStream($feedback);
                }
                $response->send();

                $this->newLine(2);
                $count++;
            }

            $this->info('Analysis complete for all feedback items.');
        }

        return self::SUCCESS;
    }
}
