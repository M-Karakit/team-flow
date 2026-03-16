<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyTaskAssignee
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskAssigned $event): void
    {
        try {
            Log::info('NotifyTaskAssignee fired', [
                'task_id'  => $event->task->id,
                'assignee' => $event->assignee?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('NotifyTaskAssignee failed: ' . $e->getMessage());
        }
    }
}
