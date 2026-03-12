<?php

namespace App\Http\Controllers\Api\Label;

use App\Http\Controllers\Controller;
use App\Http\Requests\Label\AttacheLabelRequest;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Requests\Label\UpdateLabelRequest;
use App\Http\Resources\Label\LabelResource;
use App\Http\Resources\Task\TaskResource;
use App\Models\Label\Label;
use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Services\Label\LabelService;
use App\Traits\EnsuresBelongsToProject;
use Illuminate\Support\Facades\Gate;

class LabelController extends Controller
{
    use EnsuresBelongsToProject;

    public $labelService;

    public function __construct(LabelService $labelService) {
        $this->labelService = $labelService;
    }

    public function index(Project $project) {
        Gate::authorize('viewAny', [Label::class, $project]);

        $data = $this->labelService->getLabels($project);
        return LabelResource::collection($data);
    }

    public function store(StoreLabelRequest $request, Project $project) {
        Gate::authorize('create', [Label::class, $project]);

        $data = $request->validated();
        $label = $this->labelService->createLabel($project, $data);
        return new LabelResource($label);
    }

    public function update(UpdateLabelRequest $request, Project $project, Label $label) {
        $this->ensureLabelBelongsToProject($project, $label);
        Gate::authorize('update', $label);

        $label = $this->labelService->updateLabel($label, $request->validated());

        return new LabelResource($label);
    }

    public function destroy(Project $project, Label $label) {
        $this->ensureLabelBelongsToProject($project, $label);
        Gate::authorize('delete', $label);

        $this->labelService->deleteLabel($label);

        return response()->json(['message' => 'Label deleted successfully.'], 200);
    }

    public function attachToTask(AttacheLabelRequest $request, Project $project, Task $task) {
        $this->ensureTaskBelongsToProject($project, $task);

        Gate::authorize('update', $task);

        $task = $this->labelService->attachLabels(
            $task,
            $request->validated('label_ids'),
            $project
        );

        return new TaskResource($task);
    }

    public function detachFromTask(Project $project, Task $task, Label $label) {
        $this->ensureLabelBelongsToProject($project, $label);
        $this->ensureLabelBelongsToProject($project, $label);
        
        Gate::authorize('update', $task);

        $task = $this->labelService->detachLabel($task, $label);

        return new TaskResource($task);
    }
}
