<?php

namespace App\Providers;

use App\Models\Attachment\Attachment;
use App\Models\Label\Label;
use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Policies\AttachmentPolicy;
use App\Policies\CommentPolicy;
use App\Policies\LabelPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use Dom\Comment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Label::class, LabelPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
    }
}
