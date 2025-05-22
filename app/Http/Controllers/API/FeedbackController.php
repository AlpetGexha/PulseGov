<?php

namespace App\Http\Controllers\API;

use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\FeedbackRequest;
use App\Http\Resources\API\FeedbackResource;
use App\Jobs\ProcessFeedbackAIAnalysis;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResource
     */
    public function index(Request $request)
    {
        // Default to user's own feedback if not an admin
        if (Auth::user()->role !== 'admin') {
            $feedback = Feedback::where('user_id', Auth::id())
                ->latest()
                ->paginate(10);

            // Return the paginated data with the proper structure
            return FeedbackResource::collection($feedback)
                ->additional(['message' => 'Feedback retrieved successfully']);
        }

        // Admin can see all feedback with filters
        $query = Feedback::query()->with(['user', 'aIAnalysis']);

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('feedback_type')) {
            $query->where('feedback_type', $request->feedback_type);
        }

        if ($request->has('urgency_level')) {
            $query->where('urgency_level', $request->urgency_level);
        }

        if ($request->has('department')) {
            $query->where('department_assigned', $request->department);
        }

        $feedback = $query->latest()->paginate(15);

        // Return paginated data with proper structure using Laravel API Resources
        return FeedbackResource::collection($feedback)
            ->additional(['message' => 'Feedback retrieved successfully']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FeedbackRequest $request
     * @return JsonResponse
     */
    public function store(FeedbackRequest $request)
    {
        // Create feedback with validated data
        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'location' => $request->location,
            'service' => $request->service,
            'status' => FeedbackStatus::UNDER_REVIEW->value,
            'feedback_type' => $request->feedback_type ?? FeedbackType::SUGGESTION->value,
            'tracking_code' => $this->generateTrackingCode(),
        ]);

        // Trigger AI analysis job to process the feedback
        \App\Jobs\ProcessFeedbackAIAnalysis::dispatch($feedback);

        return response()->json([
            'data' => new FeedbackResource($feedback),
            'message' => 'Feedback submitted successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $feedback = Feedback::findOrFail($id);

        // Check if user can view this feedback
        if (Auth::user()->role !== 'admin' && Auth::id() !== $feedback->user_id) {
            return response()->json([
                'message' => 'You are not authorized to view this feedback',
            ], 403);
        }

        // Eager load relationships
        $feedback->load(['user', 'aIAnalysis']);

        return response()->json([
            'data' => new FeedbackResource($feedback),
            'message' => 'Feedback retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FeedbackRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(FeedbackRequest $request, string $id)
    {
        $feedback = Feedback::findOrFail($id);

        // Check if user can update this feedback
        if (Auth::user()->role !== 'admin' && Auth::id() !== $feedback->user_id) {
            return response()->json([
                'message' => 'You are not authorized to update this feedback',
            ], 403);
        }

        // Only allow users to update certain fields if they're not admin
        if (Auth::user()->role !== 'admin') {
            // Regular users can only update title, body, location, service
            $feedback->update($request->only([
                'title', 'body', 'location', 'service'
            ]));
        } else {
            // Admins can update all fields
            $feedback->update($request->validated());
        }

        return response()->json([
            'data' => new FeedbackResource($feedback),
            'message' => 'Feedback updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        $feedback = Feedback::findOrFail($id);

        // Only admins can delete feedback
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'You are not authorized to delete feedback',
            ], 403);
        }

        $feedback->delete();

        return response()->json([
            'message' => 'Feedback deleted successfully',
        ]);
    }

    /**
     * Generate a unique tracking code for feedback.
     *
     * @return string
     */
    private function generateTrackingCode(): string
    {
        $prefix = 'FB-';
        $randomString = Str::random(8);
        $timestamp = now()->format('Ymd');

        return $prefix . $timestamp . '-' . strtoupper($randomString);
    }

    /**
     * Analyze feedback using AI.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function analyze(string $id)
    {
        // Get feedback
        $feedback = Feedback::findOrFail($id);

        // Check if user can trigger analysis of this feedback
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'You are not authorized to analyze this feedback',
            ], 403);
        }

        // Dispatch the job to analyze the feedback
        ProcessFeedbackAIAnalysis::dispatch($feedback);

        return response()->json([
            'message' => 'Feedback analysis has been queued',
        ]);
    }
}
