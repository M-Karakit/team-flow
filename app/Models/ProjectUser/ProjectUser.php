<?php

namespace App\Models\ProjectUser;

use App\Models\Project\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{
    protected $table = 'project_user';

    public $incrementing = true;

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * The project in this pivot.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user in this pivot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
