<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Project\MemberController;
use App\Http\Controllers\Api\Project\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
    });
});
