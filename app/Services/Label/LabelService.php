<?php

namespace App\Services\Label;

use App\Models\Label\Label;
use App\Models\Project\Project;
use App\Models\Task\Task;

class LabelService
{
    public function getLabels(Project $project) {
        return $project->labels()->get();
    }

    public function createLabel(Project $project, array $data) {
        if ($project->labels()->where('name', $data['name'])->exists()) {
            throw new \Exception('Label with this name already exists in the project.');
        }

        return $project->labels()->create($data);
    }

    public function updateLabel(Label $label, array $data) {
        if (isset($data['name'])) {
            $exists = $label->project->labels()
                            ->where('name', $data['name'])
                            ->where('id', '!=', $label->id)
                            ->exists();

            if ($exists) {
                throw new \Exception("A label with this name already exists in this project.");
            }
        }

        $label->update($data);
        return $label;
    }

    public function deleteLabel(Label $label) {
        $label->tasks()->detach();
        $label->delete();
    }

    public function attachLabels(Task $task, array $labelIds, Project $project) {
        $validLabelIds = $project->labels()
                        ->whereIn('id', $labelIds)
                        ->pluck('id')
                        ->toArray();

        if (count($validLabelIds) !== count($labelIds)) {
            throw new \Exception('One or more labels are invalid for this project.');
        }

        $task->labels()->sync($validLabelIds);
        $task->load('labels');
        return $task;
    }

    public function detachLabel(Task $task, Label $label) {
        $task->labels()->detach($label->id);
        $task->load('labels');
        return $task;
    }
}
