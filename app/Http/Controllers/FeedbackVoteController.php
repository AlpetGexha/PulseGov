<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\VoteType;
use App\Http\Requests\FeedbackVoteRequest;
use App\Models\Feedback;
use App\Models\FeedbackVote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class FeedbackVoteController extends Controller
{
    /**
     * Store or update a vote for feedback.
     */
    public function vote(FeedbackVoteRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $feedback = Feedback::findOrFail($validated['feedback_id']);
            $userId = Auth::id();

            // Find existing vote
            $vote = FeedbackVote::where('feedback_id', $feedback->id)
                ->where('user_id', $userId)
                ->first();

            if ($vote) {
                // If same vote type, remove it (toggle off)
                if ($vote->vote->value === $validated['vote']) {
                    $vote->delete();

                    return back()->with('success', 'Vote removed successfully');
                }

                // If different vote type, update it
                $vote->update(['vote' => $validated['vote']]);

                return back()->with('success', 'Vote updated successfully');
            }

            // Create new vote
            FeedbackVote::create([
                'feedback_id' => $feedback->id,
                'user_id' => $userId,
                'vote' => $validated['vote'],
            ]);

            return back()->with('success', 'Vote recorded successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to record vote: ' . $e->getMessage());
        }
    }

    /**
     * Get vote counts for feedback (optional AJAX endpoint).
     */
    public function getVoteCounts(Feedback $feedback): JsonResponse
    {
        $upvotes = FeedbackVote::where('feedback_id', $feedback->id)
            ->where('vote', VoteType::UPVOTE->value)
            ->count();

        $downvotes = FeedbackVote::where('feedback_id', $feedback->id)
            ->where('vote', VoteType::DOWNVOTE->value)
            ->count();

        $userVote = null;
        if (Auth::check()) {
            $vote = FeedbackVote::where('feedback_id', $feedback->id)
                ->where('user_id', Auth::id())
                ->first();

            $userVote = $vote ? $vote->vote->value : null;
        }

        return response()->json([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            'total' => $upvotes - $downvotes,
            'userVote' => $userVote,
        ]);
    }
}
