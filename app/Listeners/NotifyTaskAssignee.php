<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
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
            $event->assignee->notify(
                new TaskAssignedNotification($event->task)
            );
        } catch (\Exception $e) {
            Log::error('NotifyTaskAssignee failed: ' . $e->getMessage());
        }
    }
}
