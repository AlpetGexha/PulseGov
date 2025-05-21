<?php

namespace App\Http\Controllers;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Http\Requests\FeedbackRequest;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feedbacks = Feedback::with(['user'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Feedback/Index', [
            'feedbacks' => $feedbacks,
            'statuses' => FeedbackStatus::options(),
            'feedbackTypes' => FeedbackType::options(),
            'urgencyLevels' => UrgencyLevel::options(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedbackRequest $request)
    {
        $feedback = Feedback::create(array_merge(
            $request->validated(),
            [
                'user_id' => auth()->id(),
                'tracking_code' => Str::upper(Str::random(10)),
                // Default to under_review status for new feedback
                'status' => FeedbackStatus::UNDER_REVIEW->value,
            ]
        ));

        // Process any additional categories if provided
        if ($request->has('categories')) {
            $feedback->feedbackCategories()->sync($request->categories);
        }

        return redirect()->route('feedback.show', $feedback)
            ->with('success', 'Feedback submitted successfully with tracking code: ' . $feedback->tracking_code);
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        $feedback->load(['user', 'feedbackCategories.category', 'feedbackStatuses']);

        return Inertia::render('Feedback/Show', [
            'feedback' => $feedback,
            'statuses' => FeedbackStatus::options(),
            'statusColors' => collect(FeedbackStatus::cases())->mapWithKeys(fn($status) => [$status->value => $status->color()])->toArray(),
            'statusIcons' => collect(FeedbackStatus::cases())->mapWithKeys(fn($status) => [$status->value => $status->icon()])->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeedbackRequest $request, Feedback $feedback)
    {
        $feedback->update($request->validated());

        if ($request->has('categories')) {
            $feedback->feedbackCategories()->sync($request->categories);
        }

        return redirect()->route('feedback.show', $feedback)
            ->with('success', 'Feedback updated successfully.');
    }
}
