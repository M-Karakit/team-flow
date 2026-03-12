<?php

namespace App\Policies;

use App\Models\Label\Label;
use App\Models\Project\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LabelPolicy
{
    private function canManageLabels(User $user, Project $project): bool {
        if ($user->hasRole('admin')) return true;

        return $project->members()
                       ->where('user_id', $user->id)
                       ->wherePivot('role', 'manager')
                       ->exists();
    }

    public function viewAny(User $user, Project $project): Response {
        $isMember = $project->members()->where('user_id', $user->id)->exists();

        return $isMember || $user->hasRole('admin')
            ? Response::allow()
            : Response::deny('You are not a member of this project.');
    }

    public function create(User $user, Project $project): Response {
        return $this->canManageLabels($user, $project)
            ? Response::allow()
            : Response::deny('You do not have permission to create labels.');
    }

    public function update(User $user, Label $label): Response {
        return $this->canManageLabels($user, $label->project)
            ? Response::allow()
            : Response::deny('You do not have permission to update this label.');
    }

    public function delete(User $user, Label $label): Response {
        return $this->canManageLabels($user, $label->project)
            ? Response::allow()
            : Response::deny('You do not have permission to delete this label.');
    }
}
