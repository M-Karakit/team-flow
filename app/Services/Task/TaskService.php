<?php

namespace App\Services\Task;

use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function listTasks(Project $project, array $filters = []) {
        $query = $project->tasks()->with('assignee', 'labels');

        if (!empty($filters['assigned_to'])) {
            $query->byAssignee($filters['assigned_to']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (!empty($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }

        if (!empty($filters['due_date_from']) || !empty($filters['due_date_to'])) {
            $query->byDueDateRange(
                $filters['due_date_from'] ?? null,
                $filters['due_date_to']   ?? null,
            );
        }

        if (!empty($filters['due'])) {
            match($filters['due']) {
                'overdue'   => $query->overdue(),
                'today'     => $query->dueToday(),
                'this_week' => $query->dueThisWeek(),
            };
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }


    public function createTask(Project $project, array $data, User $authUser) {
        if (!empty($data['assigned_to'])) {
            $this->validateAssignee($project, $data['assigned_to'], $authUser);
        }

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

    public function getTask(Task $task) {
        return $task->load('project', 'assignee', 'labels');
    }

    public function updateTask(Task $task, array $data, User $authUser) {

        if (!empty($data['assigned_to']) && $data['assigned_to'] !== $task->assigned_to) {
            $this->validateAssignee($task->project, $data['assigned_to'], $authUser);
        }

        if (!$authUser->can('edit any task') && !$authUser->can('edit assigned task')) {
            $data = array_intersect_key($data, ['status' => '']);
        }

        return DB::transaction(function () use ($task, $data) {
            $task->update($data);

            if (isset($data['labels'])) {
                $task->labels()->sync($data['labels']);
            }

            return $task->fresh(['assignee', 'labels']);
        });
    }

    public function getTrashedTasks(Project $project) {
        return $project->tasks()
                ->with('assignee', 'labels')
                ->onlyTrashed()
                ->paginate(15);
    }

    public function restoreTask(Task $task): Task {
        $task->restore();
        return $task->fresh(['assignee', 'labels', 'project']);
    }

    public function forceDeleteTask(Task $task): void {
        $task->forceDelete();
    }

    private function validateAssignee(Project $project, int $assigneeId, User $authUser): void {
        $assignee = $project->members()->where('user_id', $assigneeId)->first();

        if (!$assignee) {
            throw new \Exception('You can only assign tasks to project members.');
        }

        $roleHierarchy = [
            'admin'       => 4,
            'manager'     => 3,
            'team-leader' => 2,
            'member'      => 1,
        ];

        $authUserLevel   = $roleHierarchy[$authUser->getRoleNames()->first()] ?? 0;
        $assigneeLevel   = $roleHierarchy[$assignee->getRoleNames()->first()] ?? 0;

        if (!$authUser->hasRole('admin') && $assigneeLevel >= $authUserLevel) {
            throw new \Exception('You cannot assign tasks to users with equal or higher roles.');
        }
    }
}
