<?php

namespace App\Policies;

use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    private function isMemberOf(User $user, Task $task): bool
    {
        $project = $task->project; 
        return $project->members()->where('user_id', $user->id)->exists();
    }


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): Response
    {
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        if (!$this->isMemberOf($user, $task)) {
            return Response::deny('You are not a member of this project.');
        }

        return $user->can('view projects')
            ? Response::allow()
            : Response::deny('You do not have permission to view this task.');
        }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): Response
    {
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        if (!$project->members()->where('user_id', $user->id)->exists()) {
            return Response::deny('You are not a member of this project.');
        }

        return $user->can('create tasks')
            ? Response::allow()
            : Response::deny('You do not have permission to create tasks.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): Response
    {
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        if (!$this->isMemberOf($user, $task)) {
            return Response::deny('You are not a member of this project.');
        }

        if ($user->can('edit any task')) {
            return Response::allow();
        }

        if ($user->can('edit assigned task') && $task->assigned_to === $user->id) {
            return Response::allow();
        }

        if ($user->can('update task status') && $task->assigned_to === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this task.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): Response
    {
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        if (!$this->isMemberOf($user, $task)) {
            return Response::deny('You are not a member of this project.');
        }

        return $user->can('delete tasks')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this task.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): Response
    {
        if ($user->hasRole('admin')) {
            return Response::allow();
        }

        if (!$this->isMemberOf($user, $task)) {
            return Response::deny('You are not a member of this project.');
        }

        return $user->can('delete tasks')
            ? Response::allow()
            : Response::deny('You do not have permission to restore this task.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): Response
    {
        return $user->hasRole('admin')
        ? Response::allow()
        : Response::deny('Only admins can permanently delete tasks.');
    }
}
