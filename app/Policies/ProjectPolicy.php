<?php

namespace App\Policies;

use App\Models\Project\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): Response
    {
        $allowed = $user->hasRole('admin')
            || $project->owner_id === $user->id
            || $project->members()->where('user_id', $user->id)->exists();

        return $allowed
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to view project');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->hasRole('admin') || $user->can('create projects')
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to create project');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): Response
    {
        $allowed = $user->hasRole('admin')
            || $project->owner_id === $user->id
            || $project->members()
                        ->where('user_id', $user->id)
                        ->wherePivot('role', 'manager')
                        ->exists();

        return $allowed
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to update project');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): Response
    {
        $allowed = $user->hasRole('admin') || $project->owner_id === $user->id;

        return $allowed
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to delete project');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): Response
    {
        $allowed = $user->hasRole('admin') || $project->owner_id === $user->id;

        return $allowed
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to restore project');
    }

    public function viewTrashed(User $user): Response
    {
        return $user->hasRole('admin') || $user->can('view trashed projects')
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to view trashed projects');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): Response
    {
        $allowed = $user->hasRole('admin') || $project->owner_id === $user->id;

        return $allowed
        ? Response::allow()
        : Response::denyWithStatus(403, 'You do not have permission to force delete project');
    }
}
