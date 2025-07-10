<?php

namespace App\Jobs;

use App\Models\Feedback;
use App\Enum\FeedbackSentiment;
use App\Enum\UrgencyLevel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateAIAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The maximum number of retries for this job.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The user who initiated the job.
     *
     * @var int
     */
    private $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('🚀 Starting AI analytics generation job', [
                'user_id' => $this->userId,
                'job_id' => $this->job->getJobId()
            ]);

            // Update status
            $this->updateStatus('Retrieving feedback data...', 10);

            // Get feedback data
            $feedbackData = Feedback::with(['aIAnalysis', 'user'])
                ->whereNotNull('sentiment')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            if ($feedbackData->isEmpty()) {
                throw new \Exception('No feedback data available for analysis');
            }

            // Update status
            $this->updateStatus('Generating prioritized topics...', 30);

            // Generate prioritized topics
            $prioritizedTopics = $this->generatePrioritizedTopics($feedbackData);

            // Update status
            $this->updateStatus('Generating recommendations...', 60);

            // Generate recommendations
            $aiRecommendations = $this->generateAIRecommendations($prioritizedTopics);

            // Update status
            $this->updateStatus('Preparing final analysis...', 80);

            // Generate complete analytics data
            $analyticsData = $this->generateAnalyticsDataWithAI($prioritizedTopics, $aiRecommendations);

            // Cache results
            $cacheKey = 'ai_analytics_data';
            $cacheDuration = 3600; // 1 hour

            Cache::put($cacheKey, $analyticsData, $cacheDuration);

            // Set final success status
            Cache::put('ai_analytics_status', [
                'status' => 'completed',
                'message' => 'AI analysis completed successfully!',
                'completed_at' => now()->toISOString(),
                'progress' => 100
            ], 3600);

            Log::info('✅ AI analytics generation completed', [
                'user_id' => $this->userId,
                'topics_count' => count($prioritizedTopics),
                'recommendations_count' => count($aiRecommendations)
            ]);

        } catch (\Exception $e) {
            Log::error('❌ AI analytics generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId
            ]);

            Cache::put('ai_analytics_status', [
                'status' => 'failed',
                'message' => 'Analysis failed: ' . $e->getMessage(),
                'failed_at' => now()->toISOString()
            ], 3600);

            throw $e;
        }
    }

    /**
     * Update the job status in cache.
     */
    private function updateStatus(string $message, int $progress): void
    {
        Cache::put('ai_analytics_status', [
            'status' => 'processing',
            'message' => $message,
            'started_at' => now()->toISOString(),
            'progress' => $progress
        ], 3600);

        Log::info('AI analysis status updated', [
            'message' => $message,
            'progress' => $progress
        ]);
    }

    /**
     * Generate AI-prioritized topics using OpenAI.
     */
    private function generatePrioritizedTopics($feedbackData): array
    {
        $prompt = $this->buildPrioritizationPrompt($feedbackData);

        Log::info('Calling OpenAI API for topic prioritization...', [
            'model' => 'gpt-4o',
            'prompt_length' => strlen($prompt)
        ]);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant for government analytics. Your task is to analyze citizen feedback and prioritize topics for government action. Return a JSON array of topics with detailed analysis.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'response_format' => [
                'type' => 'json_object'
            ],
            'temperature' => 0.3,
            'max_tokens' => 4000,
        ]);

        Log::info('✅ OpenAI API response received', [
            'response_length' => strlen($response->choices[0]->message->content ?? ''),
            'tokens_used' => $response->usage->totalTokens ?? 0
        ]);

        $aiResponse = json_decode($response->choices[0]->message->content, true);

        if (isset($aiResponse['prioritized_topics']) && is_array($aiResponse['prioritized_topics'])) {
            return $aiResponse['prioritized_topics'];
        }

        throw new \Exception('Invalid response format from OpenAI API');
    }

    /**
     * Build prioritization prompt for OpenAI.
     */
    private function buildPrioritizationPrompt($feedbackData): string
    {
        $summaryData = [
            'total_feedback' => $feedbackData->count(),
            'sentiment_breakdown' => [
                'positive' => $feedbackData->where('sentiment', FeedbackSentiment::POSITIVE)->count(),
                'negative' => $feedbackData->where('sentiment', FeedbackSentiment::NEGATIVE)->count(),
                'neutral' => $feedbackData->where('sentiment', FeedbackSentiment::NEUTRAL)->count(),
            ],
            'urgency_breakdown' => [
                'critical' => $feedbackData->where('urgency_level', UrgencyLevel::CRITICAL)->count(),
                'high' => $feedbackData->where('urgency_level', UrgencyLevel::HIGH)->count(),
                'medium' => $feedbackData->where('urgency_level', UrgencyLevel::MEDIUM)->count(),
                'low' => $feedbackData->where('urgency_level', UrgencyLevel::LOW)->count(),
            ],
            'department_distribution' => $feedbackData->groupBy('department_assigned')->map->count()->toArray(),
            'top_feedback_samples' => $feedbackData->take(20)->map(function ($feedback) {
                return [
                    'title' => $feedback->title,
                    'body' => substr($feedback->body, 0, 200) . '...',
                    'sentiment' => $feedback->sentiment?->value,
                    'urgency' => $feedback->urgency_level?->value,
                    'department' => $feedback->department_assigned,
                    'location' => $feedback->location,
                    'created_at' => $feedback->created_at->format('Y-m-d'),
                ];
            })->toArray(),
        ];

        return "Based on the following citizen feedback data, prioritize the top 15 topics that require government attention.

Data Summary:
" . json_encode($summaryData, JSON_PRETTY_PRINT) . "

Please return a JSON object with the following structure:
{
    \"prioritized_topics\": [
        {
            \"id\": \"unique_id\",
            \"topic\": \"Clear topic name\",
            \"category\": \"Category (infrastructure, safety, environment, etc.)\",
            \"description\": \"Brief description of the issue\",
            \"urgency_score\": 0-100,
            \"sentiment_score\": 0-100,
            \"frequency\": \"Number of related reports\",
            \"impact_score\": 0-100,
            \"priority_score\": 0-100,
            \"department\": \"Responsible department\",
            \"feedback_count\": \"Number of feedback items\",
            \"locations\": [\"Location 1\", \"Location 2\"],
            \"timeframe\": \"Recent timeframe\",
            \"trend\": \"up/down/stable\",
            \"recommended_action\": \"Specific action recommendation\",
            \"ai_summary\": \"AI analysis summary\",
            \"related_keywords\": [\"keyword1\", \"keyword2\"],
            \"citizen_voices\": {
                \"positive\": 0-100,
                \"negative\": 0-100,
                \"neutral\": 0-100
            }
        }
    ]
}

Focus on actionable insights that help government officials make informed decisions. Consider urgency, citizen impact, resource requirements, and potential for quick wins.";
    }

    /**
     * Generate AI recommendations based on prioritized topics.
     */
    private function generateAIRecommendations(array $prioritizedTopics): array
    {
        $prompt = "Based on the following prioritized citizen feedback topics, provide actionable recommendations for government officials:

Topics: " . json_encode($prioritizedTopics, JSON_PRETTY_PRINT) . "

Please return a JSON object with the following structure:
{
    \"immediate_actions\": [\"Action 1\", \"Action 2\", \"Action 3\"],
    \"long_term_strategies\": [\"Strategy 1\", \"Strategy 2\", \"Strategy 3\"],
    \"resource_allocation\": [\"Allocation 1\", \"Allocation 2\", \"Allocation 3\"],
    \"communication_strategies\": [\"Communication 1\", \"Communication 2\", \"Communication 3\"]
}

Focus on specific, actionable recommendations that address the highest priority topics.";

        Log::info('Calling OpenAI API for recommendations...', [
            'model' => 'gpt-4o',
            'prompt_length' => strlen($prompt)
        ]);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant for government strategy. Provide practical, actionable recommendations based on citizen feedback analysis.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'response_format' => [
                'type' => 'json_object'
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000,
        ]);

        Log::info('✅ OpenAI recommendations response received', [
            'response_length' => strlen($response->choices[0]->message->content ?? ''),
            'tokens_used' => $response->usage->totalTokens ?? 0
        ]);

        $aiResponse = json_decode($response->choices[0]->message->content, true);

        if ($aiResponse && is_array($aiResponse)) {
            return $aiResponse;
        }

        throw new \Exception('Invalid response format from OpenAI API');
    }

    /**
     * Generate analytics data with AI results integrated.
     */
    private function generateAnalyticsDataWithAI($prioritizedTopics, $aiRecommendations): array
    {
        // Get basic statistics
        $totalFeedback = Feedback::count();
        $analyzedFeedback = Feedback::whereNotNull('sentiment')->count();

        // Get sentiment distribution
        $sentimentDistribution = [
            'positive' => Feedback::where('sentiment', FeedbackSentiment::POSITIVE)->count(),
            'negative' => Feedback::where('sentiment', FeedbackSentiment::NEGATIVE)->count(),
            'neutral' => Feedback::where('sentiment', FeedbackSentiment::NEUTRAL)->count(),
        ];

        // Get urgency distribution
        $urgencyDistribution = [
            'critical' => Feedback::where('urgency_level', UrgencyLevel::CRITICAL)->count(),
            'high' => Feedback::where('urgency_level', UrgencyLevel::HIGH)->count(),
            'medium' => Feedback::where('urgency_level', UrgencyLevel::MEDIUM)->count(),
            'low' => Feedback::where('urgency_level', UrgencyLevel::LOW)->count(),
        ];

        // Get other analytics data
        $departmentWorkload = Feedback::select('department_assigned', DB::raw('count(*) as count'))
            ->whereNotNull('department_assigned')
            ->groupBy('department_assigned')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'department_assigned')
            ->toArray();

        $locationHotspots = Feedback::select('location', DB::raw('count(*) as count'), DB::raw('AVG(CASE
            WHEN urgency_level = "critical" THEN 4
            WHEN urgency_level = "high" THEN 3
            WHEN urgency_level = "medium" THEN 2
            ELSE 1 END) as avg_urgency'))
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'location' => $item->location,
                    'count' => $item->count,
                    'avg_urgency' => round($item->avg_urgency, 1),
                ];
            })
            ->toArray();

        $topConcerns = Feedback::whereIn('sentiment', [FeedbackSentiment::NEGATIVE])
            ->whereIn('urgency_level', [UrgencyLevel::HIGH, UrgencyLevel::CRITICAL])
            ->whereNotNull('department_assigned')
            ->select('department_assigned', DB::raw('count(*) as concern_count'))
            ->groupBy('department_assigned')
            ->orderBy('concern_count', 'desc')
            ->limit(5)
            ->pluck('department_assigned')
            ->toArray();

        $trendingTopics = Feedback::where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('department_assigned')
            ->select('department_assigned', DB::raw('count(*) as recent_count'))
            ->groupBy('department_assigned')
            ->get()
            ->take(5)
            ->map(function ($item) {
                return [
                    'topic' => $item->department_assigned,
                    'change' => 0, // Simplified for now
                    'trend' => 'stable',
                ];
            })
            ->values()
            ->toArray();

        // Calculate metrics
        $avgResponseTime = 24; // Simplified
        $resolutionRate = $totalFeedback > 0 ? (Feedback::where('status', 'resolved')->count() / $totalFeedback) * 100 : 0;
        $satisfaction = $analyzedFeedback > 0 ? ($sentimentDistribution['positive'] / $analyzedFeedback) * 100 : 0;

        return [
            'prioritized_topics' => $prioritizedTopics,
            'insights' => [
                'total_feedback' => $totalFeedback,
                'analyzed_feedback' => $analyzedFeedback,
                'top_concerns' => $topConcerns,
                'sentiment_distribution' => $sentimentDistribution,
                'urgency_distribution' => $urgencyDistribution,
                'department_workload' => $departmentWorkload,
                'location_hotspots' => $locationHotspots,
                'trending_topics' => $trendingTopics,
            ],
            'ai_recommendations' => $aiRecommendations,
            'performance_metrics' => [
                'response_time' => round($avgResponseTime, 1),
                'resolution_rate' => round($resolutionRate, 1),
                'citizen_satisfaction' => round($satisfaction, 1),
                'engagement_rate' => 75.0, // Simplified
            ],
            'generated_at' => now()->toISOString(),
            'ai_generated' => true,
        ];
    }
}
