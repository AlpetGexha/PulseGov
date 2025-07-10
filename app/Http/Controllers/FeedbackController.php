<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Http\Requests\FeedbackRequest;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feedbacks = Feedback::with(['user', 'comments'])
            ->where('is_public', true) // Only show public feedback
            ->latest()
            ->paginate(15);

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
        $validated = $request->validated();

        $feedback = Feedback::create(array_merge(
            $validated,
            [
                'user_id' => Auth::id(),
                'tracking_code' => Str::upper(Str::random(10)),
                'is_public' => $validated['is_public'] ?? false, // Default to private
                // Default to under_review status for new feedback
                'status' => FeedbackStatus::UNDER_REVIEW->value,
            ]
        ));

        return redirect()->route('feedback.show', $feedback)
            ->with('success', 'Feedback submitted successfully with tracking code: ' . $feedback->tracking_code);
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        $feedback->load([
            'user',
            'feedbackCategories.category',
            'feedbackStatuses',
            'votes',
            'comments' => function ($query) {
                $query->with(['user', 'replies.user'])
                    ->whereNull('parent_id')
                    ->latest();
            },
        ]);

        return Inertia::render('Feedback/Show', [
            'feedback' => $feedback,
            'statuses' => FeedbackStatus::options(),
            'statusColors' => collect(FeedbackStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->color()])->toArray(),
            'statusIcons' => collect(FeedbackStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->icon()])->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeedbackRequest $request, Feedback $feedback)
    {
        $feedback->update($request->validated());

        return redirect()->route('feedback.show', $feedback)
            ->with('success', 'Feedback updated successfully.');
    }
}
