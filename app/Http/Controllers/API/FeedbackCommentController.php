<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\FeedbackCommentRequest;
use App\Http\Resources\API\FeedbackCommentResource;
use App\Models\Feedback;
use App\Models\FeedbackComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FeedbackCommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(FeedbackCommentRequest $request): JsonResponse
    {
        $feedback = Feedback::findOrFail($request->feedback_id);

        $comment = FeedbackComment::create([
            'feedback_id' => $feedback->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);        // Load user relationship for the response
        $comment->load('user');

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => new FeedbackCommentResource($comment),
        ], 201);
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(FeedbackCommentRequest $request, FeedbackComment $comment): JsonResponse
    {
        if (Gate::denies('update', $comment)) {
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        }

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => new FeedbackCommentResource($comment),
        ]);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(FeedbackComment $comment): JsonResponse
    {
        if (Gate::denies('delete', $comment)) {
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Toggle the pinned status of a comment.
     */
    public function togglePin(FeedbackComment $comment): JsonResponse
    {
        if (Gate::denies('update', $comment)) {
            return response()->json(['message' => 'You are not authorized to pin this comment'], 403);
        }

        $comment->update([
            'is_pinned' => ! $comment->is_pinned,
        ]);

        return response()->json([
            'message' => $comment->is_pinned ?
                'Comment pinned successfully' :
                'Comment unpinned successfully',
            'is_pinned' => $comment->is_pinned,
        ]);
    }

    /**
     * Get all comments for a feedback item.     *
     */
    public function getComments(Feedback $feedback): JsonResponse
    {
        $comments = FeedbackComment::with(['user', 'replies.user'])
            ->where('feedback_id', $feedback->id)
            ->whereNull('parent_id')  // Get only top-level comments
            ->orderBy('is_pinned', 'desc')  // Pinned comments first
            ->orderBy('created_at', 'desc')  // Then by creation date
            ->get();

        return response()->json([
            'comments' => FeedbackCommentResource::collection($comments),
        ]);
    }
}
