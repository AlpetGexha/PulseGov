<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeedbackStatusRequest;
use App\Models\Feedback;
use App\Models\FeedbackStatus;

class FeedbackStatusController extends Controller
{
    /**
     * Store a newly created feedback status.
     */
    public function store(FeedbackStatusRequest $request)
    {
        $feedbackStatus = FeedbackStatus::create(array_merge(
            $request->validated(),
            [
                'admin_id' => auth()->id(),
                'user_id' => auth()->id(),
                'changed_at' => now(),
            ]
        ));

        // Update the parent feedback's status
        $feedback = Feedback::findOrFail($request->feedback_id);
        $feedback->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'Feedback status updated successfully.');
    }

    /**
     * Update the specified feedback status.
     */
    public function update(FeedbackStatusRequest $request, FeedbackStatus $feedbackStatus)
    {
        $feedbackStatus->update(array_merge(
            $request->validated(),
            [
                'admin_id' => auth()->id(),
                'user_id' => auth()->id(),
                'changed_at' => now(),
            ]
        ));

        // Update the parent feedback's status
        $feedback = Feedback::findOrFail($feedbackStatus->feedback_id);
        $feedback->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'Feedback status updated successfully.');
    }
}
