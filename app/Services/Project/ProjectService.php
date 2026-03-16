<?php

namespace App\Services\Project;

use App\Events\ProjectCreated;
use App\Helper\CacheKey;
use App\Models\Project\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function listProjects(array $filters = []) {
        $cacheKey = CacheKey::userProjects(auth('api')->id()) . ':' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $query = Project::query()
                            ->with(['owner', 'members'])
                            ->withCount('tasks');

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
        });
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

            Cache::tags(["user." . auth('api')->id() . ".projects"])->flush();

            event(new ProjectCreated($project, auth('api')->user()));

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

            Cache::tags(["user." . auth('api')->id() . ".projects"])->flush();
            Cache::forget(CacheKey::projectStats($project->id));

            return $project->fresh(['owner', 'members']);
        });
    }

    public function getProjectStats(Project $project): array {
        $cacheKey = CacheKey::projectStats($project->id);

        return Cache::remember($cacheKey, 3600, function () use ($project) {
            return [
                'total_tasks'   => $project->tasks()->count(),
                'todo'          => $project->tasks()->byStatus('todo')->count(),
                'in_progress'   => $project->tasks()->byStatus('in_progress')->count(),
                'in_review'     => $project->tasks()->byStatus('in_review')->count(),
                'done'          => $project->tasks()->byStatus('done')->count(),
                'overdue'       => $project->tasks()->overdue()->count(),
                'total_members' => $project->members()->count(),
            ];
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
