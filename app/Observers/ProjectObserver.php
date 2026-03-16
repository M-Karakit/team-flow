<?php

namespace App\Observers;

use App\Models\ActivityLog\ActivityLog;
use App\Models\Project\Project;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        try {
            ActivityLog::create([
                'subject_id'   => $project->id,
                'subject_type' => Project::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'created project',
                'properties'   => ['name' => $project->name],
            ]);
        } catch (\Exception $e) {
            Log::error('Project observer created failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        try {
            $changed = array_keys($project->getChanges());
            $ignore  = ['updated_at'];
            $meaningful = array_diff($changed, $ignore);

            if (empty($meaningful)) return;

            ActivityLog::create([
                'subject_id'   => $project->id,
                'subject_type' => Project::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'updated project',
                'properties'   => json_encode([
                    'changed' => $project->getChanges(),
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('ProjectObserver updated failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        try {
            ActivityLog::create([
                'subject_id'   => $project->id,
                'subject_type' => Project::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'deleted project',
                'properties'   => null,
            ]);
        } catch (\Exception $e) {
            Log::error('ProjectObserver deleted failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        try {
            ActivityLog::create([
                'subject_id'   => $project->id,
                'subject_type' => Project::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'restored project',
                'properties'   => null,
            ]);
        } catch (\Exception $e) {
            Log::error('ProjectObserver restored failed: ' . $e->getMessage());
        }
    }
}
