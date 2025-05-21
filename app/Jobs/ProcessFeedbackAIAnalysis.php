<?php

namespace App\Jobs;

use App\Actions\AnalyzeFeedback;
use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFeedbackAIAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Feedback $feedback
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AnalyzeFeedback $analyzer): void
    {
        try {
            // Skip if already analyzed
            if ($this->feedback->aIAnalysis()->exists()) {
                Log::info('Feedback already analyzed', [
                    'feedback_id' => $this->feedback->id,
                ]);
                return;
            }

            // Use the action class to analyze feedback
            $analyzer->handle($this->feedback);
            
            Log::info('Feedback analyzed successfully', [
                'feedback_id' => $this->feedback->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error analyzing feedback', [
                'feedback_id' => $this->feedback->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
