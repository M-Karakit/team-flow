<?php

namespace App\Services\Comment;

use App\Models\Comment\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommentService
{
    public function getComments(Model $commentable) {
        return $commentable->comments()
                           ->with('user', 'replies.user')
                           ->whereNull('parent_id')
                           ->latest()
                           ->get();
    }

    public function createComment(Model $commentable, array $data, User $user) {
        if (!empty($data['parent_id'])) {
            $parentComment = $commentable->comments()
                                         ->where('id', $data['parent_id'])
                                         ->exists();

            if (!$parentComment) {
                throw new \Exception('Parent comment does not belong to this resource.');
            }
        }

        return $commentable->comments()->create([
            'user_id'   => $user->id,
            'body'      => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    public function updateComment(Comment $comment, array $data) {
        $comment->update($data);
        return $comment;
    }

    public function deleteComment(Comment $comment): void {
        $comment->delete();
    }
}
