<?php

namespace App\Services\Project;

use App\Models\Project\Project;
use App\Models\User;

use function Symfony\Component\Clock\now;

class MemberService
{
    public function assignMember(Project $project, $userId, $role, User $authUser) {

        $newMember = User::findOrFail($userId);

        $this->validateMemberRole($project, $newMember, $authUser);

        if ($project->members()->where('user_id', $userId)->exists()) {
            throw new \Exception("User is already a member of this project.");
        }

        $project->members()->attach($userId, [
            'role' => $role ?? 'member',
            'joined_at' => now()
        ]);
    }

    public function updateRole(Project $project, User $user, $newRole, User $authUser) {
        if ($project->owner_id === $user->id) {
            throw new \Exception("Cannot update the role of the project owner.");
        }

        if (!$project->members()->where('user_id', $user->id)->exists()) {
            throw new \Exception("User is not a member of this project.");
        }

        $this->validateMemberRole($project, $user, $authUser);

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

    private function validateMemberRole(Project $project, User $newMember, User $authUser): void
    {
        $roleHierarchy = [
            'admin'       => 4,
            'manager'     => 3,
            'team-leader' => 2,
            'member'      => 1,
        ];

        $authUserLevel  = $roleHierarchy[$authUser->getRoleNames()->first()] ?? 0;
        $newMemberLevel = $roleHierarchy[$newMember->getRoleNames()->first()] ?? 0;

        if (!$authUser->hasRole('admin') && $newMemberLevel >= $authUserLevel) {
            throw new \Exception('You cannot add users with equal or higher roles to the project.');
        }
    }
}
