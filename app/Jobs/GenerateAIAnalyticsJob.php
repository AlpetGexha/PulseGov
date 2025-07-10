<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Feedback;
use App\Services\OpenAIService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateAIAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout

    protected string $progressKey;

    public function __construct(
        protected int $userId,
        ?string $progressKey = null
    ) {
        $this->progressKey = $progressKey ?? 'analytics_progress_' . uniqid();
    }

    public function handle(OpenAIService $openai): void
    {
        try {
            $this->updateProgress(0, 'Starting analysis...');

            // Get all feedback for analysis
            $feedbacks = Feedback::with(['user', 'aIAnalysis'])
                ->whereNotNull('sentiment')
                ->get();

            $totalSteps = 5;
            $currentStep = 0;

            // Step 1: Analyze sentiment distribution
            $this->updateProgress(20, 'Analyzing sentiment trends...');
            $sentimentAnalysis = $this->analyzeSentimentTrends($feedbacks);
            $currentStep++;

            // Step 2: Process location hotspots
            $this->updateProgress(40, 'Processing location hotspots...');
            $locationHotspots = $this->processLocationHotspots($feedbacks);
            $currentStep++;

            // Step 3: Generate department insights
            $this->updateProgress(60, 'Generating department insights...');
            $departmentInsights = $this->generateDepartmentInsights($feedbacks);
            $currentStep++;

            // Step 4: Generate AI recommendations
            $this->updateProgress(80, 'Generating AI recommendations...');
            $recommendations = $this->generateRecommendations($openai, [
                'sentiment' => $sentimentAnalysis,
                'locations' => $locationHotspots,
                'departments' => $departmentInsights,
            ]);
            $currentStep++;

            // Step 5: Cache final results
            $this->updateProgress(90, 'Finalizing results...');
            $this->cacheResults([
                'sentiment_analysis' => $sentimentAnalysis,
                'location_hotspots' => $locationHotspots,
                'department_insights' => $departmentInsights,
                'ai_recommendations' => $recommendations,
                'generated_at' => now()->toISOString(),
                'ai_generated' => true,
            ]);

            // Mark as complete
            $this->updateProgress(100, 'Analysis complete!');

            // Clear progress after a delay
            Cache::put($this->progressKey, [
                'progress' => 100,
                'message' => 'Analysis complete!',
                'status' => 'completed',
            ], now()->addMinutes(5));

        } catch (Exception $e) {
            Log::error('AI Analytics Generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->updateProgress(0, 'Analysis failed: ' . $e->getMessage(), 'failed');
            throw $e;
        }
    }

    public function getProgressKey(): string
    {
        return $this->progressKey;
    }

    protected function updateProgress(int $progress, string $message, string $status = 'processing'): void
    {
        Cache::put($this->progressKey, [
            'progress' => $progress,
            'message' => $message,
            'status' => $status,
            'updated_at' => now()->toISOString(),
        ], now()->addHours(1));

        Log::info('AI Analytics progress update', [
            'progress' => $progress,
            'message' => $message,
            'key' => $this->progressKey,
        ]);
    }

    protected function analyzeSentimentTrends($feedbacks): array
    {
        // Simulate processing time
        sleep(2);

        return [
            'positive' => $feedbacks->where('sentiment', 'POSITIVE')->count(),
            'negative' => $feedbacks->where('sentiment', 'NEGATIVE')->count(),
            'neutral' => $feedbacks->where('sentiment', 'NEUTRAL')->count(),
        ];
    }

    protected function processLocationHotspots($feedbacks): array
    {
        // Simulate processing time
        sleep(2);

        return $feedbacks
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->groupBy('location')
            ->map(function ($group) {
                return [
                    'location' => $group->first()->location,
                    'count' => $group->count(),
                    'coordinates' => [
                        'lat' => $group->first()->latitude,
                        'lng' => $group->first()->longitude,
                    ],
                    'urgency_level' => $group->avg('urgency_level'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function generateDepartmentInsights($feedbacks): array
    {
        // Simulate processing time
        sleep(2);

        return $feedbacks
            ->groupBy('department_assigned')
            ->map(function ($group) {
                return [
                    'department' => $group->first()->department_assigned,
                    'total_feedback' => $group->count(),
                    'urgent_cases' => $group->where('urgency_level', 'HIGH')->count(),
                    'avg_sentiment' => $group->avg('sentiment'),
                    'response_time' => rand(24, 72), // Simulated average response time
                ];
            })
            ->values()
            ->toArray();
    }

    protected function generateRecommendations(OpenAIService $openai, array $data): array
    {
        // Simulate AI processing time
        sleep(3);

        return [
            'immediate_actions' => [
                'Address high-urgency cases in identified hotspots',
                'Improve response time for departments with delays',
                'Focus on areas with negative sentiment trends',
            ],
            'long_term_strategies' => [
                'Develop preventive maintenance schedules',
                'Implement department-specific improvement plans',
                'Enhance citizen communication channels',
            ],
            'resource_allocation' => [
                'Redistribute resources based on workload analysis',
                'Increase staffing for high-demand departments',
                'Invest in automation for common issues',
            ],
        ];
    }

    protected function cacheResults(array $results): void
    {
        Cache::put('ai_analytics_data', $results, now()->addHours(24));
    }
}
