<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileStorageTrait
{
    use ApiResponseTrait;

    private const ALLOWED_FILES = [
        'img' => [
            'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'extensions' => ['jpeg', 'png', 'gif', 'jpg', 'webp']
        ],
        'vid' => [
            'mimes' => ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-ms-wmv'],
            'extensions' => ['mp4', 'webm', 'ogg', 'mov', 'wmv']
        ],
        'aud' => [
            'mimes' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/aac'],
            'extensions' => ['mp3', 'wav', 'ogg', 'aac']
        ],
        'docs' => [
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ],
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
        ]
    ];

    /**
     * Store file and protect the site
     *
     * @param mixed $file The uploaded file
     * @param string $folderName The folder to upload the file to
     * @param string $suffix The file type suffix (img|vid|aud|docs)
     * @return string The file URL
     * @throws Exception
     */
    public function storeFile(mixed $file, string $folderName, string $suffix): string
    {
        if (!isset(self::ALLOWED_FILES[$suffix])) {
            throw new \RuntimeException(trans('general.wrong data'));
        }

        $this->validateFile($file, $suffix);

        $extension = $file->getClientOriginalExtension();
        $fileName = $this->generateSecureFileName($extension);

        $path = $this->storeFileAndValidatePath($file, $folderName, $fileName, $extension);

        return Storage::url($path);
    }

    /**
     * Delete a file from storage
     *
     * @param string $fileUrl The URL of the file to delete (e.g., /storage/attachments/post/1/file.jpg)
     * @return bool True if the file was deleted, false otherwise
     */
    public function deleteFile(string $fileUrl): bool
    {
        // Convert the URL to a storage path by removing the '/storage/' prefix
        $storagePath = str_replace('/storage/', '', $fileUrl);
        // Delete the file from the 'public' disk
        return Storage::disk('public')->delete($storagePath);
    }

    /**
     * Validate uploaded file
     *
     * @param mixed $file
     * @param string $suffix
     * @throws Exception
     */
    private function validateFile(mixed $file, string $suffix): void
    {
        $originalName = $file->getClientOriginalName();
        if (strpos($originalName, '..') !== false || preg_match('/\.[^.]+\./', $originalName)) {
            throw new \RuntimeException(trans('general.notAllowedAction'), 403);
        }

        $mimeType = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();

        if (!in_array($mimeType, self::ALLOWED_FILES[$suffix]['mimes'], true) ||
            !in_array($extension, self::ALLOWED_FILES[$suffix]['extensions'], true)) {
            throw new \RuntimeException(trans('general.invalidFileType'), 403);
        }
    }

    /**
     * Generate secure file name
     *
     * @param string $extension
     * @return string
     */
    private function generateSecureFileName(): string
    {
        return preg_replace(
            '/[^A-Za-z0-9_\-]/',
            '',
            Str::random(32)
        );
    }

    /**
     * Store file and validate path
     *
     * @param mixed $file
     * @param string $folderName
     * @param string $fileName
     * @param string $extension
     * @return string
     * @throws Exception
     */
    private function storeFileAndValidatePath(mixed $file, string $folderName, string $fileName, string $extension): string
    {
        $path = $file->storeAs($folderName, "$fileName.$extension", 'public');

        $expectedPath = storage_path("app/public/$folderName/$fileName.$extension");
        $actualPath = storage_path("app/public/$path");

        if ($actualPath !== $expectedPath) {
            Storage::disk('public')->delete($path);
            throw new \RuntimeException(trans('general.notAllowedAction'), 403);
        }

        return $path;
    }
}
