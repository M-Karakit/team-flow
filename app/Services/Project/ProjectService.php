<?php

namespace App\Services\Project;

use App\Models\Project\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function listProjects(array $filters = []) {
        $query = Project::query()->with('owner', 'members')->withCount('tasks');

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['owner_id'])) {
            $query->byOwner($filters['owner_id']);
        }

        if (!empty($filters['member_id'])) {
            $query->byMember($filters['member_id']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function createProject(array $data) {
        return DB::transaction(function () use ($data) {
            $project = Project::create([
                'owner_id' => auth('api')->id(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'due_date' => $data['due_date'] ?? null,
            ]);

            $project->members()->attach(auth('api')->id(), [
                'role' => 'manager',
                'joined_at' => now(),
            ]);

            return $project;
        });
    }

    public function showProject(Project $project) {
        return $project->load('owner', 'members', 'tasks');
    }

    public function updateProject(Project $project, array $data): Project {
        return DB::transaction(function () use ($project, $data) {
            $project->update($data);

            if (isset($data['members'])) {
                $syncData = [];

                foreach ($data['members'] as $member) {
                    $syncData[$member['id']] = [
                        'role'      => $member['role'] ?? 'member',
                        'joined_at' => now(),
                    ];
                }

                $syncData[$project->owner_id] = [
                    'role'      => 'manager',
                    'joined_at' => $project->members()
                                        ->where('user_id', $project->owner_id)
                                        ->value('joined_at') ?? now(),
                ];

                $project->members()->sync($syncData);
            }

            return $project->fresh(['owner', 'members']);
        });
    }

    public function getTrashedProjects() {
        return Project::onlyTrashed()
                        ->with('owner', 'members')
                        ->withCount('tasks')
                        ->paginate(15);
    }

    public function restoreProject(Project $project) {
        $project->restore();
        return $project->fresh(['owner', 'members']);
    }

    public function forceDeleteProject(Project $project): void{
        $project->forceDelete();
    }
}
