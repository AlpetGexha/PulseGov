<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Enum\VoteType;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\FeedbackVoteRequest;
use App\Models\Feedback;
use App\Models\FeedbackVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class FeedbackVoteController extends Controller
{
    /**
     * Store or update a vote for feedback.
     */
    public function vote(FeedbackVoteRequest $request): JsonResponse
    {;
        $feedback = Feedback::findOrFail($request->feedback_id);
        $userId = Auth::id();

        // Find existing vote
        $vote = FeedbackVote::where('feedback_id', $feedback->id)
            ->where('user_id', $userId)
            ->first();

        if ($vote) {
            // If same vote type, remove it (toggle off)
            if ($vote->vote->value === $request->vote) {
                $vote->delete();

                return response()->json([
                    'message' => 'Vote removed successfully',
                    'voteStatus' => null,
                ]);
            }

            // If different vote type, update it
            $vote->update(['vote' => $request->vote]);

            return response()->json([
                'message' => 'Vote updated successfully',
                'voteStatus' => $request->vote,
            ]);
        }

        // Create new vote
        FeedbackVote::create([
            'feedback_id' => $feedback->id,
            'user_id' => $userId,
            'vote' => $request->vote,
        ]);

        return response()->json([
            'message' => 'Vote recorded successfully',
            'voteStatus' => $request->vote,
        ], 201);
    }

    /**
     * Get vote counts for feedback.
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
