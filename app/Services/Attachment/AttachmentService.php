<?php

namespace App\Services\Attachment;

use App\Models\Attachment\Attachment;
use App\Models\User;
use App\Traits\FileStorageTrait;
use Illuminate\Database\Eloquent\Model;

class AttachmentService
{
    use FileStorageTrait;
    public function getAttachments(Model $attachable) {
        return $attachable->attachments()->with('uploadedBy')->get();
    }

    public function storeAttachment(Model $attachable, $file, User $user) {
        $mimeType = $file->getMimeType();

        $suffix = match (true) {
            str_starts_with($mimeType, 'image/') => 'img',
            str_starts_with($mimeType, 'video/') => 'vid',
            str_starts_with($mimeType, 'audio/') => 'aud',
            default => 'docs'
        };

        $folder = 'attachments/' . strtolower(class_basename($attachable)) . '/' . $attachable->id . '/' . $suffix;

        $url = $this->storeFile($file, $folder, $suffix);

        return $attachable->attachments()->create([
            'file_path'   => $url,
            'file_name'   => $file->getClientOriginalName(),
            'file_size'   => $file->getSize(),
            'mime_type'   => $mimeType,
            'uploaded_by' => $user->id,
        ]);
    }

    public function deleteAttachment(Attachment $attachment): void
    {
        $this->deleteFile($attachment->file_path);

        $attachment->delete();
    }
}
