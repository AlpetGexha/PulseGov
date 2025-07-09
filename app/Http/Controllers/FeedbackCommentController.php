<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackCommentRequest;
use App\Models\FeedbackComment;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FeedbackCommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created comment in storage.
     */
    public function store(FeedbackCommentRequest $request, Feedback $feedback): RedirectResponse
    {
        $validated = $request->validated();

        $comment = FeedbackComment::create([
            'feedback_id' => $feedback->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(FeedbackCommentRequest $request, FeedbackComment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validated();

        $comment->update([
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(FeedbackComment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Toggle the pinned status of a comment.
     */
    public function togglePin(FeedbackComment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            'is_pinned' => !$comment->is_pinned,
        ]);

        return back()->with('success', $comment->is_pinned ?
            'Comment pinned successfully.' :
            'Comment unpinned successfully.'
        );
    }
}
