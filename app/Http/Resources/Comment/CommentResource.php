<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'body' => $this->body,
            'user' => new UserResource($this->whenLoaded('user')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
        ];
    }
}
