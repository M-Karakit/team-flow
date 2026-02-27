<?php

namespace App\Models\ActivityLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * The subject of the activity (Task, Project, etc.).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The causer of the activity (usually a User).
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
