<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'tasks_count' => $this->whenCounted('tasks'),
            'tasks' => $this->whenLoaded('tasks'),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
