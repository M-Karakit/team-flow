<?php


namespace App\Traits;

use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Models\Label\Label;

trait EnsuresBelongsToProject
{
    private function ensureTaskBelongsToProject(Project $project, Task $task): void
    {
        if ($task->project_id !== $project->id) {
            abort(404, 'Task does not belong to this project.');
        }
    }

    private function ensureLabelBelongsToProject(Project $project, Label $label): void
    {
        if ($label->project_id !== $project->id) {
            abort(404, 'Label does not belong to this project.');
        }
    }
}
