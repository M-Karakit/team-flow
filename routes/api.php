<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Comment\CommentController;
use App\Http\Controllers\Api\Label\LabelController;
use App\Http\Controllers\Api\Project\MemberController;
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\Task\TaskController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::apiResource('projects', ProjectController::class);
        Route::get('projects-trashed', [ProjectController::class, 'listTrashedProjects']);
        Route::post('project-restore/{project}', [ProjectController::class, 'restoreProject'])->withTrashed();
        Route::delete('projects-force-delete/{project}', [ProjectController::class, 'forceDeleteProject']);

        Route::prefix('projects/{project}/members')->group(function () {
            Route::get('/', [MemberController::class, 'index']);
            Route::post('/', [MemberController::class, 'assignMember']);
            Route::put('/{user}', [MemberController::class, 'updateRole']);
            Route::delete('/{user}', [MemberController::class, 'removeMember']);
        });

        Route::prefix('projects/{project}/tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('/trashed', [TaskController::class, 'listTrashedTasks']);
            Route::get('/{task}', [TaskController::class, 'show']);
            Route::put('/{task}', [TaskController::class, 'update']);
            Route::delete('/{task}', [TaskController::class, 'destroy']);
            Route::post('/{task}/restore', [TaskController::class, 'restore'])->withTrashed();
            Route::delete('/{task}/force-delete', [TaskController::class, 'forceDelete'])->withTrashed();
        });

        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'update']);
        Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar']);

        Route::prefix('project/{project}')->group(function () {
            Route::apiResource('labels', LabelController::class);
            Route::post('tasks/{task}/labels', [LabelController::class, 'attachToTask']);
            Route::delete('tasks/{task}/labels/{label}', [LabelController::class, 'detachFromTask']);

            Route::get('comments', [CommentController::class, 'projectComments']);
            Route::post('comments', [CommentController::class, 'storeOnProject']);

            Route::get('tasks/{task}/comments', [CommentController::class, 'taskComments']);
            Route::post('tasks/{task}/comments', [CommentController::class, 'storeOnTask']);
        });

        Route::put('comments/{comment}',  [CommentController::class, 'update']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    });
});
