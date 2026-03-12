<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\FilterTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskResource;
use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Services\Task\TaskService;
use App\Traits\EnsuresBelongsToProject;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    use EnsuresBelongsToProject;

    public $taskService;

    public function __construct(TaskService $taskService) {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(FilterTaskRequest $request, Project $project)
    {
        $filters = $request->validated();

        $result = $this->taskService->listTasks($project, $filters);

        return TaskResource::collection($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        Gate::authorize('create', [Task::class, $project]);

        $data = $request->validated();

        $result = $this->taskService->createTask($project, $data, auth('api')->user());

        return response()->json([
            'message' => 'Task created successfully',
            'task' => new TaskResource($result)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Task $task)
    {
       $this->ensureTaskBelongsToProject($project, $task);
       Gate::authorize('view', $task);

       $result = $this->taskService->getTask($task);

       return response()->json(['task' => new TaskResource($result)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $this->ensureTaskBelongsToProject($project, $task);
        Gate::authorize('update', $task);

        $data = $request->validated();

        $result = $this->taskService->updateTask($task, $data, auth('api')->user());

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => new TaskResource($result)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Task $task)
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json([], 204);
    }

    public function listTrashedTasks(Project $project) {
        $tasks = $this->taskService->getTrashedTasks($project);

        return TaskResource::collection($tasks);
    }

    public function restore(Project $project, Task $task) {
        Gate::authorize('restore', $task);
        $restored = $this->taskService->restoreTask($task);
        return new TaskResource($restored);
    }

    public function forceDelete(Project $project, Task $task) {

        Gate::authorize('forceDelete', $task);
        $this->taskService->forceDeleteTask($task);
        return response()->json([], 204);
    }
}
