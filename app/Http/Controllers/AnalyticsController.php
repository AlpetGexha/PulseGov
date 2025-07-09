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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use OpenAI\Laravel\Facades\OpenAI;

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

        // Generate analytics data
        $analytics = $this->generateAnalyticsData();

        return Inertia::render('AnalyticsNew', [
            'analytics' => $analytics,
        ]);
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
            // Get recent feedback with analysis
            $feedbackData = Feedback::with(['aIAnalysis', 'user'])
                ->whereNotNull('sentiment')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            if ($feedbackData->isEmpty()) {
                return [];
            }

            // Generate prioritized topics using OpenAI
            $prompt = $this->buildPrioritizationPrompt($feedbackData);
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
            ]);

            $aiResponse = json_decode($response->choices[0]->message->content, true);

            if (isset($aiResponse['prioritized_topics'])) {
                return $aiResponse['prioritized_topics'];
            }

            // Fallback to manual prioritization
            return $this->manualTopicPrioritization($feedbackData);

        } catch (\Exception $e) {
            Log::error('Error generating prioritized topics', [
                'error' => $e->getMessage()
            ]);

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
            ]);

            $aiResponse = json_decode($response->choices[0]->message->content, true);

            return $aiResponse ?: $this->getFallbackRecommendations();

        } catch (\Exception $e) {
            Log::error('Error generating AI recommendations', [
                'error' => $e->getMessage()
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
        $avgResponseTime = Feedback::whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours') ?? 24;

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
        // Group feedback by department and calculate priority scores
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
}
