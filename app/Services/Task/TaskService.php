<?php

namespace App\Services\Task;

use App\Models\Project\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function createTask(Project $project, array $data) {
        return DB::transaction(function () use ($project, $data) {
            $lastOrder = $project->tasks()->max('order') ?? 0;

            return $project->tasks()->create([
                'created_by' => Auth::id(),
                'assigned_to' => $data['assigned_to'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => 'todo',
                'priority' => $data['priority'] ?? 'medium',
                'due_date' => $data['due_date'] ?? null,
                'order' => $lastOrder + 1,
            ]);
        });
    }
}
