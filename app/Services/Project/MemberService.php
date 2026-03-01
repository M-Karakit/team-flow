<?php

namespace App\Services\Project;

use App\Models\Project\Project;
use App\Models\User;

use function Symfony\Component\Clock\now;

class MemberService
{
    public function assignMember(Project $project, $userId, $role) {
        if ($project->members()->where('user_id', $userId)->exists()) {
            throw new \Exception("User is already a member of this project.");
        }

        $project->members()->attach($userId, [
            'role' => $role ?? 'member',
            'joined_at' => now()
        ]);
    }

    public function updateRole(Project $project, User $user, $newRole) {
        if ($project->owner_id === $user->id) {
            throw new \Exception("Cannot update the role of the project owner.");
        }

        if (!$project->members()->where('user_id', $user->id)->exists()) {
            throw new \Exception("User is not a member of this project.");
        }

        $project->members()->updateExistingPivot($user->id, [
            'role' => $newRole
        ]);
    }

    public function removeMember(Project $project, User $user) {
        if ($project->owner_id === $user->id) {
            throw new \Exception("Cannot remove the project owner.");
        }

        if (!$project->members()->where('user_id', $user->id)->exists()) {
            throw new \Exception("User is not a member of this project.");
        }

        $project->members()->detach($user->id);
    }
}
