<?php

namespace App\Http\Controllers\Api\Attachment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Resources\Attachment\AttachmentResource;
use App\Models\Attachment\Attachment;
use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Services\Attachment\AttachmentService;
use App\Traits\EnsuresBelongsToProject;
use Illuminate\Support\Facades\Gate;

class AttachmentController extends Controller
{
    use EnsuresBelongsToProject;

    public $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function projectAttachments(Project $project) {
        Gate::authorize('view', $project);

        $result = $this->attachmentService->getAttachments($project);

        return AttachmentResource::collection($result);
    }

    public function taskAttachments(Project $project, Task $task) {
        $this->ensureTaskBelongsToProject($project, $task);

        Gate::authorize('view', $task);

        $result = $this->attachmentService->getAttachments($task);

        return AttachmentResource::collection($result);
    }

    public function storeOnProject(StoreAttachmentRequest $request, Project $project) {
        Gate::authorize('create', [Attachment::class, $project]);
        Gate::authorize('view', $project);

        $attachment = $this->attachmentService->storeAttachment(
            $project,
            $request->file('file'),
            auth('api')->user());

        return response()->json([
            'message' => 'Attachment created successfully.',
            'attachment' => new AttachmentResource($attachment)
        ], 201);
    }

    public function storeOnTask(StoreAttachmentRequest $request, Project $project, Task $task) {
        $this->ensureTaskBelongsToProject($project, $task);
        Gate::authorize('create', Attachment::class);
        Gate::authorize('view', $task);

        $attachment = $this->attachmentService->storeAttachment(
            $task,
            $request->file('file'),
            auth('api')->user()
        );

        return response()->json([
            'message' => 'Attachment created successfully.',
            'attachment' => new AttachmentResource($attachment)
        ], 201);
    }

    public function destroy(Attachment $attachment) {
        Gate::authorize('delete', $attachment);

        $this->attachmentService->deleteAttachment($attachment);

        return response()->json([], 204);
    }
}
