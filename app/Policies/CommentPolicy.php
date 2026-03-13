<?php

namespace App\Policies;

use App\Models\Comment\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->can('add comments')
            ? Response::allow()
            : Response::deny('You do not have permission to add comments.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): Response
    {
        $allowed = $user->hasRole('admin')
            || $comment->user_id === $user->id;

        return $allowed
            ? Response::allow()
            : Response::deny('You can only edit your own comments.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): Response
    {
        $allowed = $user->hasRole('admin')
            || $comment->user_id === $user->id
            || $user->hasRole('manager');

        return $allowed
            ? Response::allow()
            : Response::deny('You do not have permission to delete this comment.');
    }
}
