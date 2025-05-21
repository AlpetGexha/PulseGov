<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFeedbackAIAnalysis;
use App\Models\Feedback;
use Illuminate\Console\Command;

class AnalyzeAllFeedback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:analyze-all {--force : Force analysis of all feedback, even if already analyzed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue jobs to analyze all unanalyzed feedback';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Feedback::query();

        if (!$this->option('force')) {
            // Only get feedback without analysis
            $query->whereDoesntHave('aIAnalysis');
        }

        $feedbackCount = $query->count();

        if ($feedbackCount === 0) {
            $this->info('No unanalyzed feedback found.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($feedbackCount);
        $progressBar->start();

        $query->chunk(100, function ($feedbackItems) use ($progressBar) {
            foreach ($feedbackItems as $feedback) {
                ProcessFeedbackAIAnalysis::dispatch($feedback);
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully queued {$feedbackCount} feedback items for analysis.");

        return 0;
    }
}
