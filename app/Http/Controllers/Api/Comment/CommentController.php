<?php

namespace App\Http\Controllers\Api\Comment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment\Comment;
use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Services\Comment\CommentService;
use App\Traits\EnsuresBelongsToProject;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    use EnsuresBelongsToProject;

    public $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function projectComments(Project $project)
    {
        Gate::authorize('view', $project);

        $result = $this->commentService->getComments($project);

        return CommentResource::collection($result);
    }

    public function taskComments(Project $project, Task $task)
    {
        $this->ensureTaskBelongsToProject($project, $task);
        Gate::authorize('view', $task);

        $result = $this->commentService->getComments($task);

        return CommentResource::collection($result);
    }

    public function storeOnProject(StoreCommentRequest $request, Project $project)
    {
        Gate::authorize('create', Comment::class);
        Gate::authorize('view', $project);

        $comment = $this->commentService->createComment(
            $project,
            $request->validated(),
            auth('api')->user()
        );

        return new CommentResource($comment->load('user'));
    }

    public function storeOnTask(StoreCommentRequest $request, Project $project, Task $task)
    {
        $this->ensureTaskBelongsToProject($project, $task);
        Gate::authorize('create', Comment::class);
        Gate::authorize('view', $task);

        $comment = $this->commentService->createComment(
            $task,
            $request->validated(),
            auth('api')->user()
        );

        return new CommentResource($comment->load('user'));
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $updated = $this->commentService->updateComment($comment, $request->validated());

        return new CommentResource($updated);
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json([], 204);
    }
}
