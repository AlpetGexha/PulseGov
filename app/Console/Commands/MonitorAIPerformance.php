<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AIAnalysis;
use App\Models\AIModelMetrics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorAIPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:monitor-performance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Track and record AI analysis performance metrics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Calculating AI analysis performance metrics...');

        // Calculate average processing time
        $avgProcessingTime = AIAnalysis::select(
            DB::raw('AVG(TIMESTAMPDIFF(SECOND, a.created_at, a_i_analyses.created_at)) as avg_time')
        )
            ->join('feedback as a', 'a_i_analyses.feedback_id', '=', 'a.id')
            ->whereRaw('DATE(a_i_analyses.created_at) = CURRENT_DATE')
            ->first()
            ->avg_time ?? 0;

        // Count analyses done today
        $analysesCount = AIAnalysis::whereDate('created_at', now()->format('Y-m-d'))->count();

        // Calculate percentage of feedback that has analysis
        $totalFeedback = DB::table('feedback')->count();
        $analyzedFeedback = AIAnalysis::count();
        $coveragePercentage = $totalFeedback > 0 ? ($analyzedFeedback / $totalFeedback) * 100 : 0;

        // Record metrics
        AIModelMetrics::create([
            'date' => now()->format('Y-m-d'),
            'model_name' => 'gpt-4o',
            'avg_processing_time' => $avgProcessingTime,
            'analyses_count' => $analysesCount,
            'coverage_percentage' => $coveragePercentage,
        ]);

        $this->info('AI performance metrics recorded successfully.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Average Processing Time', round($avgProcessingTime, 2) . ' seconds'],
                ['Analyses Today', $analysesCount],
                ['Overall Coverage', round($coveragePercentage, 2) . '%'],
                ['Total Feedback', $totalFeedback],
                ['Analyzed Feedback', $analyzedFeedback],
            ]
        );

        return 0;
    }
}
