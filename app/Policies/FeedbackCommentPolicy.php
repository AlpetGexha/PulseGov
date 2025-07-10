<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FeedbackComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FeedbackCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeedbackComment $comment): Response
    {
        return $user->id === $comment->user_id || $user->role === 'admin'
            ? Response::allow()
            : Response::deny('You do not own this comment.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeedbackComment $comment): Response
    {
        return $user->id === $comment->user_id || $user->role === 'admin'
            ? Response::allow()
            : Response::deny('You do not own this comment.');
    }
}
