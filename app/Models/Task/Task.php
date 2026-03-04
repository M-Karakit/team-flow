<?php

namespace App\Models\Task;

use App\Models\Attachment\Attachment;
use App\Models\Comment\Comment;
use App\Models\Label\Label;
use App\Models\LabelTask\LabelTask;
use App\Models\Project\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'order',
    ];

    /**
     * The project this task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user assigned to this task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * The user who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Comments on this task.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Attachments on this task.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Labels assigned to this task.
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'label_task')
            ->using(LabelTask::class)
            ->withTimestamps();
    }

    public function scopeByAssignee (Builder $query, $userId): Builder {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder {
        return $query->where('priority', $priority);
    }

    public function scopeByDueDateRange(Builder $query, ?string $from, ?string $to) {
        return $query
            ->when($from, fn($q) => $q->where('due_date', '>=', $from))
            ->when($to, fn($q) => $q->where('due_date', '<=', $to));
    }

    public function scopeOverDue(Builder $query)  {
        return $query->whereDate('due_date', today())
                ->where('status', '!=', 'done');
    }

    public function scopeDueToday(Builder $query)  {
        return $query->whereDate('due_date', today());
    }

    public function scopeDueThisWeek(Builder $query): Builder {
        return $query->whereBetween('due_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }
}
