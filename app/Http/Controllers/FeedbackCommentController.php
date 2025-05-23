<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackCommentRequest;
use App\Models\FeedbackComment;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FeedbackCommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(FeedbackCommentRequest $request): RedirectResponse
    {
        $feedback = Feedback::findOrFail($request->feedback_id);

        $comment = FeedbackComment::create([
            'feedback_id' => $feedback->id,
            'user_id' => Auth::id(),
            'parent_id' => $request?->parent_id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(FeedbackCommentRequest $request, FeedbackComment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            'content' => $request->content,
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
