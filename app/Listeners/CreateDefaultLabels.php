<?php

namespace App\Listeners;

use App\Events\ProjectCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateDefaultLabels
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
    public function handle(ProjectCreated $event): void
    {

    Log::info('CreateDefaultLabels handle() called', [
        'project_id' => $event->project->id,
    ]);
        $defaultLabels = [
            ['name' => 'Bug',         'color' => '#ef4444'],
            ['name' => 'Feature',     'color' => '#3b82f6'],
            ['name' => 'Urgent',      'color' => '#f97316'],
            ['name' => 'Design',      'color' => '#8b5cf6'],
            ['name' => 'Performance', 'color' => '#10b981'],
        ];

        foreach ($defaultLabels as $label) {
            $event->project->labels()->create($label);
        }
    }
}
