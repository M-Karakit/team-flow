<?php

namespace App\Policies;

use App\Models\Attachment\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttachmentPolicy
{
    public function create(User $user): Response
    {
        return $user->can('upload attachments')
            ? Response::allow()
            : Response::deny('You do not have permission to upload attachments.');
    }

    public function delete(User $user, Attachment $attachment): Response
    {
        $allowed = $user->hasRole('admin')
            || $attachment->uploaded_by === $user->id;

        return $allowed
            ? Response::allow()
            : Response::deny('You can only delete your own attachments.');
    }
}
