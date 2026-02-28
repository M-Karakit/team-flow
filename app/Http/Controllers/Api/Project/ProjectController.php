<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\FilterRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project\Project;
use App\Services\Project\ProjectService;

class ProjectController extends Controller
{
    public $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(FilterRequest $request)
    {
        $filters = $request->validated();
        $projects = $this->projectService->listProjects($filters);

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $project = $this->projectService->createProject($data);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project = $this->projectService->showProject($project);

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        $updatedProject = $this->projectService->updateProject($project, $data);

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($updatedProject),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json([], 204);
    }

    public function listTrashedProjects() {
        $projects = $this->projectService->getTrashedProjects();
        return ProjectResource::collection($projects);
    }

    public function restoreProject(Project $project) {
        $project = $this->projectService->restoreProject($project);
        return response()->json([
            'message' => 'Project restored successfully',
            'data' => new ProjectResource($project),
        ]);
    }

    public function forceDeleteProject(Project $project) {
        $this->projectService->forceDeleteProject($project);
        return response()->json([], 204);
    }
}
