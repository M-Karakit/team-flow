<?php

namespace App\Observers;

use App\Models\ActivityLog\ActivityLog;
use App\Models\Task\Task;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        try {
            ActivityLog::create([
                'subject_id'   => $task->id,
                'subject_type' => Task::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'created task',
                'properties'   => json_encode(['title' => $task->title]),
            ]);
        } catch (\Exception $e) {
            Log::error('TaskObserver created failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        try {
            if ($task->wasChanged('status')) {
                ActivityLog::create([
                    'subject_id'   => $task->id,
                    'subject_type' => Task::class,
                    'causer_id'    => auth('api')->id(),
                    'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                    'description'  => 'changed task status',
                    'properties'   => json_encode([
                        'from' => $task->getOriginal('status'),
                        'to'   => $task->status,
                    ]),
                ]);
            }

            if ($task->wasChanged('assigned_to')) {
                ActivityLog::create([
                    'subject_id'   => $task->id,
                    'subject_type' => Task::class,
                    'causer_id'    => auth('api')->id(),
                    'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                    'description'  => 'reassigned task',
                    'properties'   => json_encode([
                        'from' => $task->getOriginal('assigned_to'),
                        'to'   => $task->assigned_to,
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TaskObserver updated failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        try {
            ActivityLog::create([
                'subject_id'   => $task->id,
                'subject_type' => Task::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'deleted task',
                'properties'   => null,
            ]);
        } catch (\Exception $e) {
            Log::error('TaskObserver deleted failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        try {
            ActivityLog::create([
                'subject_id'   => $task->id,
                'subject_type' => Task::class,
                'causer_id'    => auth('api')->id(),
                'causer_type'  => auth('api')->id() ? \App\Models\User::class : null,
                'description'  => 'restored task',
                'properties'   => null,
            ]);
        } catch (\Exception $e) {
            Log::error('TaskObserver restored failed: ' . $e->getMessage());
        }
    }
}
