<?php

namespace App\Models\LabelTask;

use App\Models\Label\Label;
use App\Models\Task\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LabelTask extends Pivot
{
    protected $table = 'label_task';

    public $incrementing = true;

    /**
     * The label in this pivot.
     */
    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    /**
     * The task in this pivot.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
