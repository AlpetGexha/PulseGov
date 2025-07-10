<?php

namespace App\Http\Controllers;

use App\Enum\FeedbackSentiment;
use App\Enum\UrgencyLevel;
use App\Jobs\GenerateAIAnalyticsJob;
use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index()
    {
        // Check if user is authorized
   //     if (Auth::user()?->role !== 'admin') {
     //       return redirect()->back()->with('error', 'You are not authorized to access this page.');
       // }

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
            $cachedData['ai_generated'] = $cachedData['ai_generated'] ?? false;

            Log::info('Using cached analytics data', [
                'cache_key' => $cacheKey,
                'generated_at' => $cachedData['generated_at']
            ]);

            return $cachedData;
        }

        // Use fallback data instead of generating fresh AI data
        $fallbackData = $this->getFallbackAnalyticsData();

        // Add indicators
        $fallbackData['is_cached'] = false;
        $fallbackData['ai_generated'] = false;
        $fallbackData['needs_ai_generation'] = true;
        $fallbackData['cache_expires_at'] = now()->addSeconds($cacheDuration)->toISOString();

        Log::info('Using fallback analytics data (no AI)', [
            'reason' => 'No cached data available'
        ]);

        return $fallbackData;
    }

    /**
     * Get current job status from cache.
     */
    private function getAnalysisStatus(): ?array
    {
        $status = Cache::get('ai_analytics_status');
        if (!$status) {
            return null;
        }

        if ($status['status'] === 'processing' && isset($status['started_at'])) {
            $startedAt = Carbon::parse($status['started_at']);
            $minutesAgo = $startedAt->diffInMinutes(now());
            if ($minutesAgo >= 10) {
                // Reset status if it's been too long
                Cache::forget('ai_analytics_status');
                return null;
            }
        }

        return $status;
    }

    /**
     * Clear cached analytics data.
     */
    public function clearCache()
    {
        try {
            $cacheKey = 'ai_analytics_data';
            Cache::forget($cacheKey);

            Cache::forget('ai_analytics_status');

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
     * Generate AI analysis on demand and cache the results.
     */
    public function generateAI()
    {
        try {
            Log::info('=== Starting AI Analysis Generation Request ===', [
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            // Check OpenAI configuration
            if (empty(config('openai.api_key'))) {
                Log::error('OpenAI API key is not configured');
                return redirect()->back()->with('error', 'OpenAI API key is not configured. Please check your environment settings.');
            }

            // Check for existing processing status
            $analysisStatus = $this->getAnalysisStatus();
            if ($analysisStatus && $analysisStatus['status'] === 'processing') {
                return redirect()->back()->with('info', 'Analysis is already in progress. Started at ' . Carbon::parse($analysisStatus['started_at'])->toTimeString() . '. Please wait for it to complete.');
            }

            // Set initial processing status
            Cache::put('ai_analytics_status', [
                'status' => 'processing',
                'message' => 'Starting AI analysis...',
                'started_at' => now()->toISOString(),
                'progress' => 0
            ], 3600);

            // Generate a unique progress key
            $progressKey = 'analytics_progress_' . uniqid();
            
            // Dispatch the background job with progress tracking
            dispatch((new GenerateAIAnalyticsJob(Auth::id(), $progressKey))->onQueue('analytics'));

            Log::info('AI analysis job dispatched', [
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success', 'AI analysis started! The process will run in the background and results will be available shortly.');
        } catch (\Exception $e) {
            Log::error('Error starting AI analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update status to failed
            Cache::put('ai_analytics_status', [
                'status' => 'failed',
                'message' => 'Failed to start analysis: ' . $e->getMessage(),
                'failed_at' => now()->toISOString()
            ], 3600);

            return redirect()->back()->with('error', 'Failed to start AI analysis. Please try again later.');
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
     * Get fallback analytics data when AI fails.
     */
    private function getFallbackAnalyticsData(): array
    {
        // Get actual basic statistics
        $totalFeedback = Feedback::count();
        $analyzedFeedback = Feedback::whereNotNull('sentiment')->count();

        // Get actual sentiment distribution
        $sentimentDistribution = [
            'positive' => Feedback::where('sentiment', FeedbackSentiment::POSITIVE)->count(),
            'negative' => Feedback::where('sentiment', FeedbackSentiment::NEGATIVE)->count(),
            'neutral' => Feedback::where('sentiment', FeedbackSentiment::NEUTRAL)->count(),
        ];

        // Get actual urgency distribution
        $urgencyDistribution = [
            'critical' => Feedback::where('urgency_level', UrgencyLevel::CRITICAL)->count(),
            'high' => Feedback::where('urgency_level', UrgencyLevel::HIGH)->count(),
            'medium' => Feedback::where('urgency_level', UrgencyLevel::MEDIUM)->count(),
            'low' => Feedback::where('urgency_level', UrgencyLevel::LOW)->count(),
        ];

        return [
            'prioritized_topics' => $this->getFallbackTopics(),
            'insights' => [
                'total_feedback' => $totalFeedback,
                'analyzed_feedback' => $analyzedFeedback,
                'top_concerns' => ['Infrastructure', 'Public Safety', 'Environment'],
                'sentiment_distribution' => $sentimentDistribution,
                'urgency_distribution' => $urgencyDistribution,
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
            'ai_generated' => false,
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
