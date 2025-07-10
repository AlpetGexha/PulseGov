<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get feedback statistics and insights.
     */
    public function getFeedbackStats(): JsonResponse
    {
        // Check if user is authorized
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'You are not authorized to access this resource',
            ], 403);
        }

        // Get counts by status
        $statusCounts = Feedback::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status->value => $item->count];
            })
            ->toArray();

        // Get counts by feedback type
        $typeCounts = Feedback::select('feedback_type', DB::raw('count(*) as count'))
            ->groupBy('feedback_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->feedback_type->value => $item->count];
            })
            ->toArray();

        // Get counts by sentiment
        $sentimentCounts = Feedback::select('sentiment', DB::raw('count(*) as count'))
            ->whereNotNull('sentiment')
            ->groupBy('sentiment')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->sentiment->value => $item->count];
            })
            ->toArray();

        // Get top departments
        $topDepartments = Feedback::select('department_assigned', DB::raw('count(*) as count'))
            ->whereNotNull('department_assigned')
            ->groupBy('department_assigned')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->department_assigned => $item->count];
            })
            ->toArray();

        // Get recent trends (last 30 days)
        $trends = Feedback::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->total];
            })
            ->toArray();

        // Get top tags
        $topTags = DB::table('a_i_analyses')
            ->select(DB::raw('JSON_EXTRACT(suggested_tags, "$[*]") as tags'))
            ->whereNotNull('suggested_tags')
            ->get()
            ->flatMap(function ($item) {
                // Extract tags from JSON array
                $tags = json_decode($item->tags ?? '[]');

                return is_array($tags) ? $tags : [];
            })
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->toArray();

        return response()->json([
            'counts' => [
                'total' => Feedback::count(),
                'analyzed' => Feedback::whereNotNull('sentiment')->count(),
                'pending' => Feedback::whereNull('sentiment')->count(),
            ],
            'byStatus' => $statusCounts,
            'byType' => $typeCounts,
            'bySentiment' => $sentimentCounts,
            'topDepartments' => $topDepartments,
            'trends' => $trends,
            'topTags' => $topTags,
        ]);
    }

    /**
     * Get summary of recent activity.
     */
    public function getRecentActivity(): JsonResponse
    {
        // Check if user is authorized
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'You are not authorized to access this resource',
            ], 403);
        }

        // Get recent feedback items with analysis
        $recentFeedback = Feedback::with(['aIAnalysis', 'user'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'title' => $feedback->title,
                    'body' => $feedback->body,
                    'tracking_code' => $feedback->tracking_code,
                    'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
                    'status' => [
                        'value' => $feedback->status->value,
                        'label' => $feedback->status->label(),
                        'color' => $feedback->status->color(),
                    ],
                    'user_name' => $feedback->user ? $feedback->user->name : 'Anonymous',
                    'sentiment' => $feedback->sentiment ? [
                        'value' => $feedback->sentiment->value,
                        'label' => $feedback->sentiment->label(),
                        'color' => $feedback->sentiment->color(),
                    ] : null,
                    'summary' => $feedback->aIAnalysis->summary ?? null,
                ];
            });

        // Get critical feedback
        $criticalFeedback = Feedback::with(['aIAnalysis', 'user'])
            ->where('urgency_level', 'critical')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'title' => $feedback->title,
                    'body' => $feedback->body,
                    'tracking_code' => $feedback->tracking_code,
                    'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
                    'department_assigned' => $feedback->department_assigned,
                    'user_name' => $feedback->user ? $feedback->user->name : 'Anonymous',
                ];
            });

        // Count stats
        $today = now()->format('Y-m-d');
        $counts = [
            'today' => Feedback::whereDate('created_at', $today)->count(),
            'positive_today' => Feedback::whereDate('created_at', $today)
                ->where('sentiment', FeedbackSentiment::POSITIVE->value)
                ->count(),
            'negative_today' => Feedback::whereDate('created_at', $today)
                ->where('sentiment', FeedbackSentiment::NEGATIVE->value)
                ->count(),
            'suggestions' => Feedback::where('feedback_type', FeedbackType::SUGGESTION->value)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return response()->json([
            'recentFeedback' => $recentFeedback,
            'criticalFeedback' => $criticalFeedback,
            'counts' => $counts,
        ]);
    }
}
