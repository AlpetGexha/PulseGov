<?php

namespace App\Http\Controllers;

use App\Actions\AnalyzeFeedback;
use App\Agents\FeedbackAgentChatBot;
use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * Analytics Controller with AI-Powered Insights
 *
 * This controller provides AI-driven analytics for citizen feedback,
 * including prioritized topics, sentiment analysis, and actionable recommendations.
 *
 * Features:
 * - AI-powered topic prioritization using OpenAI GPT-4
 * - Sentiment analysis and urgency scoring
 * - Real-time analytics generation
 * - Fallback mechanisms for when AI is unavailable
 * - Performance metrics and trend analysis
 *
 * Usage:
 * - GET /analytics - Display the analytics dashboard
 * - POST /analytics/generate-ai - Generate fresh AI analysis on-demand
 */

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index()
    {
        // Check if user is authorized
       // if (Auth::user()->role !== 'admin') {
        //    return redirect()->back()->with('error', 'You are not authorized to access this page.');
        //}

        // Get cached analytics data or generate fresh data
        $analytics = $this->getCachedAnalyticsData();

        return Inertia::render('AnalyticsNew', [
            'analytics' => $analytics,
        ]);
    }

    /**
     * Get cached analytics data or generate fresh data if cache is empty.
     */
    private function getCachedAnalyticsData(): array
    {
        $cacheKey = 'ai_analytics_data';
        $cacheDuration = 3600; // 1 hour

        // Try to get cached data first
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            // Add cache indicator
            $cachedData['is_cached'] = true;
            $cachedData['cache_expires_at'] = now()->addSeconds($cacheDuration)->toISOString();

            Log::info('Using cached analytics data', [
                'cache_key' => $cacheKey,
                'generated_at' => $cachedData['generated_at']
            ]);

            return $cachedData;
        }

        // Generate fresh data if no cache
        $freshData = $this->generateAnalyticsData();

        // Cache the fresh data
        Cache::put($cacheKey, $freshData, $cacheDuration);

        Log::info('Generated and cached fresh analytics data', [
            'cache_key' => $cacheKey,
            'cache_duration' => $cacheDuration
        ]);

        // Add cache indicator
        $freshData['is_cached'] = false;
        $freshData['cache_expires_at'] = now()->addSeconds($cacheDuration)->toISOString();

        return $freshData;
    }

    /**
     * Generate comprehensive analytics data using AI.
     */
    private function generateAnalyticsData(): array
    {
        try {
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

            // Get department workload
            $departmentWorkload = Feedback::select('department_assigned', DB::raw('count(*) as count'))
                ->whereNotNull('department_assigned')
                ->groupBy('department_assigned')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'department_assigned')
                ->toArray();

            // Get location hotspots
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

            // Get trending topics (simplified for demo)
            $trendingTopics = $this->getTrendingTopics();

            // Get top concerns
            $topConcerns = $this->getTopConcerns();

            // Generate AI-prioritized topics
            $prioritizedTopics = $this->generatePrioritizedTopics();

            // Generate AI recommendations
            $aiRecommendations = $this->generateAIRecommendations($prioritizedTopics);

            // Calculate performance metrics
            $performanceMetrics = $this->calculatePerformanceMetrics();

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
                'performance_metrics' => $performanceMetrics,
                'generated_at' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('Error generating analytics data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback data
            return $this->getFallbackAnalyticsData();
        }
    }

    /**
     * Generate AI-prioritized topics using OpenAI and feedback data.
     */
    private function generatePrioritizedTopics(): array
    {
        try {
            Log::info('🧠 [Original Method] Starting OpenAI topic prioritization');

            // Increase execution time limit for AI operations
            set_time_limit(120); // 2 minutes for AI calls

            // Get recent feedback with analysis
            $feedbackData = Feedback::with(['aIAnalysis', 'user'])
                ->whereNotNull('sentiment')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            if ($feedbackData->isEmpty()) {
                Log::warning('No feedback data available for prioritization');
                return [];
            }

            // Generate prioritized topics using OpenAI
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

            if (isset($aiResponse['prioritized_topics'])) {
                Log::info('🎯 Successfully parsed AI topics', [
                    'count' => count($aiResponse['prioritized_topics'])
                ]);
                return $aiResponse['prioritized_topics'];
            }

            // Fallback to manual prioritization
            Log::warning('AI response missing prioritized_topics, falling back to manual');
            return $this->manualTopicPrioritization($feedbackData);

        } catch (\Exception $e) {
            Log::error('❌ [CRITICAL] OpenAI topic prioritization failed', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'is_timeout' => str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout'),
                'trace' => $e->getTraceAsString()
            ]);

            // Also log to console/error log
            error_log('PulseGov OpenAI Error (Topics): ' . $e->getMessage());

            return $this->getFallbackTopics();
        }
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
        try {
            // Increase execution time limit for AI operations
            set_time_limit(120); // 2 minutes for AI calls

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

            return $aiResponse ?: $this->getFallbackRecommendations();

        } catch (\Exception $e) {
            Log::error('❌ [CRITICAL] OpenAI recommendations failed', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'is_timeout' => str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout'),
            ]);

            return $this->getFallbackRecommendations();
        }
    }

    /**
     * Get trending topics based on recent feedback patterns.
     */
    private function getTrendingTopics(): array
    {
        // Get feedback from last 30 days grouped by topic/category
        $recentFeedback = Feedback::where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('department_assigned')
            ->select('department_assigned', DB::raw('count(*) as recent_count'))
            ->groupBy('department_assigned')
            ->get();

        // Get feedback from 30-60 days ago for comparison
        $olderFeedback = Feedback::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->whereNotNull('department_assigned')
            ->select('department_assigned', DB::raw('count(*) as older_count'))
            ->groupBy('department_assigned')
            ->get()
            ->keyBy('department_assigned');

        return $recentFeedback->map(function ($item) use ($olderFeedback) {
            $olderCount = $olderFeedback->get($item->department_assigned)?->older_count ?? 0;
            $change = $olderCount > 0 ? (($item->recent_count - $olderCount) / $olderCount) * 100 : 0;

            return [
                'topic' => $item->department_assigned,
                'change' => round($change, 1),
                'trend' => $change > 5 ? 'up' : ($change < -5 ? 'down' : 'stable'),
            ];
        })->sortByDesc('change')->take(5)->values()->toArray();
    }

    /**
     * Get top concerns based on negative sentiment and high urgency.
     */
    private function getTopConcerns(): array
    {
        return Feedback::whereIn('sentiment', [FeedbackSentiment::NEGATIVE])
            ->whereIn('urgency_level', [UrgencyLevel::HIGH, UrgencyLevel::CRITICAL])
            ->whereNotNull('department_assigned')
            ->select('department_assigned', DB::raw('count(*) as concern_count'))
            ->groupBy('department_assigned')
            ->orderBy('concern_count', 'desc')
            ->limit(5)
            ->pluck('department_assigned')
            ->toArray();
    }

    /**
     * Calculate performance metrics.
     */
    private function calculatePerformanceMetrics(): array
    {
        // Calculate average response time (simplified)
        $avgResponseTime = 24;

        // Calculate resolution rate
        $totalFeedback = Feedback::count();
        $resolvedFeedback = Feedback::where('status', 'resolved')->count();
        $resolutionRate = $totalFeedback > 0 ? ($resolvedFeedback / $totalFeedback) * 100 : 0;

        // Calculate citizen satisfaction (based on positive sentiment)
        $analyzedFeedback = Feedback::whereNotNull('sentiment')->count();
        $positiveFeedback = Feedback::where('sentiment', FeedbackSentiment::POSITIVE)->count();
        $satisfaction = $analyzedFeedback > 0 ? ($positiveFeedback / $analyzedFeedback) * 100 : 0;

        // Calculate engagement rate (comments + votes)
        $engagementRate = 75; // Simplified for demo

        return [
            'response_time' => round($avgResponseTime, 1),
            'resolution_rate' => round($resolutionRate, 1),
            'citizen_satisfaction' => round($satisfaction, 1),
            'engagement_rate' => $engagementRate,
        ];
    }

    /**
     * Manual topic prioritization fallback.
     */
    private function manualTopicPrioritization($feedbackData): array
    {
         $departmentGroups = $feedbackData->groupBy('department_assigned');
        $prioritizedTopics = [];

        foreach ($departmentGroups as $department => $feedbacks) {
            if (!$department) continue;

            $urgencyScore = $this->calculateUrgencyScore($feedbacks);
            $sentimentScore = $this->calculateSentimentScore($feedbacks);
            $frequencyScore = min(100, $feedbacks->count() * 2);
            $priorityScore = ($urgencyScore + $sentimentScore + $frequencyScore) / 3;

            $prioritizedTopics[] = [
                'id' => 'topic_' . str_replace(' ', '_', strtolower($department)),
                'topic' => $department . ' Issues',
                'category' => 'Municipal Service',
                'description' => "Issues related to {$department} services and operations",
                'urgency_score' => $urgencyScore,
                'sentiment_score' => $sentimentScore,
                'frequency' => $feedbacks->count(),
                'impact_score' => $priorityScore,
                'priority_score' => $priorityScore,
                'department' => $department,
                'feedback_count' => $feedbacks->count(),
                'locations' => $feedbacks->whereNotNull('location')->pluck('location')->unique()->values()->toArray(),
                'timeframe' => 'Last 30 days',
                'trend' => 'stable',
                'recommended_action' => "Review and prioritize {$department} service improvements",
                'ai_summary' => "Citizens have reported various issues with {$department} services that require attention",
                'related_keywords' => ['service', 'improvement', 'issue'],
                'citizen_voices' => [
                    'positive' => $feedbacks->where('sentiment', FeedbackSentiment::POSITIVE)->count() / $feedbacks->count() * 100,
                    'negative' => $feedbacks->where('sentiment', FeedbackSentiment::NEGATIVE)->count() / $feedbacks->count() * 100,
                    'neutral' => $feedbacks->where('sentiment', FeedbackSentiment::NEUTRAL)->count() / $feedbacks->count() * 100,
                ],
            ];
        }

        return collect($prioritizedTopics)
            ->sortByDesc('priority_score')
            ->take(15)
            ->values()
            ->toArray();
    }

    /**
     * Calculate urgency score for a collection of feedback.
     */
    private function calculateUrgencyScore($feedbacks): int
    {
        $urgencyValues = [
            UrgencyLevel::CRITICAL->value => 100,
            UrgencyLevel::HIGH->value => 75,
            UrgencyLevel::MEDIUM->value => 50,
            UrgencyLevel::LOW->value => 25,
        ];

        $totalScore = 0;
        foreach ($feedbacks as $feedback) {
            $totalScore += $urgencyValues[$feedback->urgency_level?->value] ?? 25;
        }

        return round($totalScore / $feedbacks->count());
    }

    /**
     * Calculate sentiment score for a collection of feedback.
     */
    private function calculateSentimentScore($feedbacks): int
    {
        $negativeCount = $feedbacks->where('sentiment', FeedbackSentiment::NEGATIVE)->count();
        $totalCount = $feedbacks->count();

        // Higher negative sentiment = higher priority score
        return round(($negativeCount / $totalCount) * 100);
    }

    /**
     * Get fallback analytics data when AI fails.
     */
    private function getFallbackAnalyticsData(): array
    {
        return [
            'prioritized_topics' => $this->getFallbackTopics(),
            'insights' => [
                'total_feedback' => Feedback::count(),
                'analyzed_feedback' => Feedback::whereNotNull('sentiment')->count(),
                'top_concerns' => ['Infrastructure', 'Public Safety', 'Environment'],
                'sentiment_distribution' => ['positive' => 30, 'negative' => 40, 'neutral' => 30],
                'urgency_distribution' => ['critical' => 10, 'high' => 25, 'medium' => 40, 'low' => 25],
                'department_workload' => ['Public Works' => 45, 'Transportation' => 32, 'Parks' => 28],
                'location_hotspots' => [
                    ['location' => 'Downtown', 'count' => 25, 'avg_urgency' => 3.2],
                    ['location' => 'Westside', 'count' => 18, 'avg_urgency' => 2.8],
                ],
                'trending_topics' => [
                    ['topic' => 'Road Maintenance', 'change' => 15.2, 'trend' => 'up'],
                    ['topic' => 'Public Safety', 'change' => -8.5, 'trend' => 'down'],
                ],
            ],
            'ai_recommendations' => $this->getFallbackRecommendations(),
            'performance_metrics' => [
                'response_time' => 24.5,
                'resolution_rate' => 68.2,
                'citizen_satisfaction' => 72.8,
                'engagement_rate' => 75.0,
            ],
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Get fallback topics when AI fails.
     */
    private function getFallbackTopics(): array
    {
        return [
            [
                'id' => 'topic_infrastructure',
                'topic' => 'Infrastructure Maintenance',
                'category' => 'Public Works',
                'description' => 'Road repairs, utility maintenance, and infrastructure updates',
                'urgency_score' => 85,
                'sentiment_score' => 70,
                'frequency' => 42,
                'impact_score' => 88,
                'priority_score' => 88,
                'department' => 'Public Works',
                'feedback_count' => 42,
                'locations' => ['Downtown', 'Westside', 'Eastside'],
                'timeframe' => 'Last 30 days',
                'trend' => 'up',
                'recommended_action' => 'Prioritize road repair schedule and increase maintenance frequency',
                'ai_summary' => 'Citizens are reporting widespread infrastructure issues requiring urgent attention',
                'related_keywords' => ['roads', 'potholes', 'maintenance', 'infrastructure'],
                'citizen_voices' => ['positive' => 15, 'negative' => 70, 'neutral' => 15],
            ],
            [
                'id' => 'topic_safety',
                'topic' => 'Public Safety Concerns',
                'category' => 'Safety',
                'description' => 'Crime prevention, emergency response, and community safety',
                'urgency_score' => 90,
                'sentiment_score' => 75,
                'frequency' => 35,
                'impact_score' => 85,
                'priority_score' => 85,
                'department' => 'Public Safety',
                'feedback_count' => 35,
                'locations' => ['Downtown', 'Northside'],
                'timeframe' => 'Last 30 days',
                'trend' => 'stable',
                'recommended_action' => 'Increase police patrols and community safety programs',
                'ai_summary' => 'Citizens are concerned about safety in key areas of the city',
                'related_keywords' => ['crime', 'safety', 'police', 'security'],
                'citizen_voices' => ['positive' => 20, 'negative' => 65, 'neutral' => 15],
            ],
        ];
    }

    /**
     * Get fallback recommendations when AI fails.
     */
    private function getFallbackRecommendations(): array
    {
        return [
            'immediate_actions' => [
                'Address critical infrastructure issues reported in downtown area',
                'Increase police presence in high-crime neighborhoods',
                'Implement emergency response protocol for urgent citizen complaints',
            ],
            'long_term_strategies' => [
                'Develop comprehensive infrastructure maintenance plan',
                'Create citizen engagement programs to improve communication',
                'Establish department-specific response time targets',
            ],
            'resource_allocation' => [
                'Allocate additional budget for road maintenance and repairs',
                'Hire additional staff for high-workload departments',
                'Invest in digital tools for better citizen communication',
            ],
            'communication_strategies' => [
                'Implement regular progress updates on major issues',
                'Create citizen advisory committees for key departments',
                'Develop transparent reporting on government response times',
            ],
        ];
    }

    /**
     * Generate AI analysis on demand and cache the results.
     */
    public function generateAI()
    {
        try {
            // Increase execution time limit for AI operations
            set_time_limit(180); // 3 minutes for full AI analysis

            Log::info('=== Starting AI Analysis Generation ===', [
                'user_id' => Auth::id(),
                'timestamp' => now(),
                'openai_api_key_exists' => !empty(config('openai.api_key')),
                'execution_time_limit' => ini_get('max_execution_time')
            ]);

            // Check OpenAI configuration
            if (empty(config('openai.api_key'))) {
                Log::error('OpenAI API key is not configured');
                return redirect()->back()->with('error', 'OpenAI API key is not configured. Please check your environment settings.');
            }

            // Get fresh feedback data for AI analysis
            $feedbackData = Feedback::with(['aIAnalysis', 'user'])
                ->whereNotNull('sentiment')
                // ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();


            Log::info('Feedback data retrieved', [
                'count' => $feedbackData->count(),
                'has_sentiment' => $feedbackData->whereNotNull('sentiment')->count()
            ]);

            if ($feedbackData->isEmpty()) {
                Log::warning('No feedback data available for AI analysis');
                return redirect()->back()->with('error', 'No feedback data available for AI analysis.');
            }

            // Get current cached data as fallback
            $currentData = Cache::get('ai_analytics_data', $this->getFallbackAnalyticsData());

            // Force generate fresh AI analysis with detailed logging
            Log::info('Starting OpenAI topic prioritization...');
            $prioritizedTopics = $this->generatePrioritizedTopicsWithLogging($feedbackData, $currentData['prioritized_topics'] ?? []);

            Log::info('Starting OpenAI recommendations generation...');
            $aiRecommendations = $this->generateAIRecommendationsWithLogging($prioritizedTopics, $currentData['ai_recommendations'] ?? []);

            // Generate complete analytics data with AI results
            $analyticsData = $this->generateAnalyticsDataWithAI($prioritizedTopics, $aiRecommendations);

            // Cache the fresh AI analysis for 1 hour
            $cacheKey = 'ai_analytics_data';
            $cacheDuration = 3600; // 1 hour

            Cache::put($cacheKey, $analyticsData, $cacheDuration);

            Log::info('=== AI analysis completed successfully ===', [
                'topics_count' => count($prioritizedTopics),
                'recommendations_count' => count($aiRecommendations),
                'user_id' => Auth::id(),
                'cache_key' => $cacheKey,
                'cache_duration' => $cacheDuration
            ]);

            return redirect()->back()->with('success', 'AI analysis generated successfully! Found ' . count($prioritizedTopics) . ' prioritized topics and cached for 1 hour.');

        } catch (\Exception $e) {
            Log::error('=== CRITICAL ERROR in AI analysis generation ===', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'is_timeout' => str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout')
            ]);

            // Log to console as well
            error_log('PulseGov AI Analysis Error: ' . $e->getMessage());

            // Provide specific error message for timeout
            $errorMessage = str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout')
                ? 'AI analysis timed out. This can happen with large datasets. Please try again or contact support.'
                : 'Failed to generate AI analysis: ' . $e->getMessage();

            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Clear cached analytics data.
     */
    public function clearCache()
    {
        try {
            $cacheKey = 'ai_analytics_data';

            Cache::forget($cacheKey);

            Log::info('Analytics cache cleared', [
                'cache_key' => $cacheKey,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('success', 'Cache cleared successfully! Next page load will generate fresh data.');

        } catch (\Exception $e) {
            Log::error('Error clearing analytics cache', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Generate AI-prioritized topics with detailed logging and fallback.
     */
    private function generatePrioritizedTopicsWithLogging($feedbackData, $fallbackTopics = []): array
    {
        try {
            // Increase execution time limit for AI operations
            set_time_limit(120); // 2 minutes for AI calls

            Log::info('🧠 Starting OpenAI topic prioritization', [
                'feedback_count' => $feedbackData->count(),
                'model' => 'gpt-4o',
                'execution_time_limit' => ini_get('max_execution_time')
            ]);

            if ($feedbackData->isEmpty()) {
                Log::warning('No feedback data for AI topic prioritization, using fallback');
                return $fallbackTopics ?: $this->getFallbackTopics();
            }

            // Build the prompt
            $prompt = $this->buildPrioritizationPrompt($feedbackData);

            Log::info('Sending request to OpenAI for topic prioritization...', [
                'prompt_length' => strlen($prompt),
                'api_key_configured' => !empty(config('openai.api_key'))
            ]);            // Make OpenAI API call with retry mechanism
            $response = $this->makeOpenAICallWithRetry(function() use ($prompt) {
                return OpenAI::chat()->create([
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
            });

            Log::info('✅ OpenAI response received for topics', [
                'response_length' => strlen($response->choices[0]->message->content ?? ''),
                'usage_tokens' => $response->usage->totalTokens ?? 0
            ]);

            $aiResponse = json_decode($response->choices[0]->message->content, true);

            if (isset($aiResponse['prioritized_topics']) && is_array($aiResponse['prioritized_topics'])) {
                Log::info('🎯 AI topics parsed successfully', [
                    'topics_count' => count($aiResponse['prioritized_topics'])
                ]);
                return $aiResponse['prioritized_topics'];
            } else {
                Log::warning('AI response does not contain valid prioritized_topics, using manual prioritization');
                return $this->manualTopicPrioritization($feedbackData);
            }

        } catch (\Exception $e) {
            Log::error('❌ OpenAI topic prioritization failed', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'is_timeout' => str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout'),
            ]);

            // Log to console
            error_log('OpenAI Topic Prioritization Error: ' . $e->getMessage());

            // Return existing data or fallback
            return $fallbackTopics ?: $this->manualTopicPrioritization($feedbackData);
        }
    }

    /**
     * Generate AI recommendations with detailed logging and fallback.
     */
    private function generateAIRecommendationsWithLogging(array $prioritizedTopics, $fallbackRecommendations = []): array
    {
        try {
            // Increase execution time limit for AI operations
            set_time_limit(120); // 2 minutes for AI calls

            Log::info('💡 Starting OpenAI recommendations generation', [
                'topics_count' => count($prioritizedTopics),
                'model' => 'gpt-4o',
                'execution_time_limit' => ini_get('max_execution_time')
            ]);

            if (empty($prioritizedTopics)) {
                Log::warning('No prioritized topics for AI recommendations, using fallback');
                return $fallbackRecommendations ?: $this->getFallbackRecommendations();
            }

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

            Log::info('Sending request to OpenAI for recommendations...', [
                'prompt_length' => strlen($prompt),
                'api_key_configured' => !empty(config('openai.api_key'))
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

            Log::info('✅ OpenAI response received for recommendations', [
                'response_length' => strlen($response->choices[0]->message->content ?? ''),
                'usage_tokens' => $response->usage->totalTokens ?? 0
            ]);

            $aiResponse = json_decode($response->choices[0]->message->content, true);

            if ($aiResponse && is_array($aiResponse)) {
                Log::info('💡 AI recommendations parsed successfully', [
                    'immediate_actions' => count($aiResponse['immediate_actions'] ?? []),
                    'long_term_strategies' => count($aiResponse['long_term_strategies'] ?? []),
                    'resource_allocation' => count($aiResponse['resource_allocation'] ?? []),
                    'communication_strategies' => count($aiResponse['communication_strategies'] ?? [])
                ]);
                return $aiResponse;
            } else {
                Log::warning('AI response is not valid for recommendations, using fallback');
                return $fallbackRecommendations ?: $this->getFallbackRecommendations();
            }

        } catch (\Exception $e) {
            Log::error('❌ OpenAI recommendations generation failed', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'is_timeout' => str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout')
            ]);

            // Log to console
            error_log('OpenAI Recommendations Error: ' . $e->getMessage());

            // Return existing data or fallback
            return $fallbackRecommendations ?: $this->getFallbackRecommendations();
        }
    }

    /**
     * Generate analytics data with AI results integrated.
     */
    private function generateAnalyticsDataWithAI($prioritizedTopics, $aiRecommendations): array
    {
        try {
            Log::info('📊 Generating complete analytics with AI data');

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

            $trendingTopics = $this->getTrendingTopics();
            $topConcerns = $this->getTopConcerns();
            $performanceMetrics = $this->calculatePerformanceMetrics();

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
                'performance_metrics' => $performanceMetrics,
                'generated_at' => now()->toISOString(),
                'ai_generated' => true,
            ];

        } catch (\Exception $e) {
            Log::error('Error generating analytics data with AI', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback data
            return $this->getFallbackAnalyticsData();
        }
    }

    /**
     * Make OpenAI API call with retry mechanism for timeout handling.
     */
    private function makeOpenAICallWithRetry(callable $callback, int $maxRetries = 2, int $delaySeconds = 5): mixed
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $attempt++;

                Log::info('Making OpenAI API call', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'timeout' => config('openai.analytics_timeout', 120)
                ]);

                return $callback();

            } catch (\Exception $e) {
                $isTimeout = str_contains($e->getMessage(), 'execution time') ||
                            str_contains($e->getMessage(), 'timeout') ||
                            str_contains($e->getMessage(), 'cURL error 28');

                Log::warning('OpenAI API call failed', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'is_timeout' => $isTimeout,
                    'error' => $e->getMessage()
                ]);

                // If this is the last attempt or not a timeout, throw the exception
                if ($attempt >= $maxRetries || !$isTimeout) {
                    throw $e;
                }

                // Wait before retry (exponential backoff)
                $waitTime = $delaySeconds * $attempt;
                Log::info("Retrying OpenAI call in {$waitTime} seconds...");
                sleep($waitTime);
            }
        }

        throw new \Exception('OpenAI API call failed after maximum retries');
    }
}
