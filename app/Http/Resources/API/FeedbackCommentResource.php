<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'content' => $this->content,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'is_pinned' => (bool) $this->is_pinned,
            'parent_id' => $this->parent_id,
        ];

        // Add user data if available
        if ($this->relationLoaded('user') && $this->user) {
            $data['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar ?? null,
            ];
        }

        // Add replies if they are loaded
        if ($this->relationLoaded('replies') && $this->replies->count() > 0) {
            $data['replies'] = FeedbackCommentResource::collection($this->replies);
        }

        return $data;
    }
}
