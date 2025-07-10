<?php

declare(strict_types=1);

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FeedbackResource extends JsonResource
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
            'title' => $this->title,
            'body' => $this->body,
            'tracking_code' => $this->tracking_code,
            'status' => [
                'value' => $this->status,
                'label' => $this->status ? $this->status->label() : null,
                'color' => $this->status ? $this->status->color() : null,
            ],
            'location' => $this->location,
            'service' => $this->service,
            'feedback_type' => [
                'value' => $this->feedback_type,
                'label' => $this->feedback_type ? $this->feedback_type->label() : null,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        // Add user data if available
        if ($this->relationLoaded('user')) {
            $data['user'] = [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ];
        }

        // Add AI analysis data if available
        if ($this->sentiment || $this->urgency_level || $this->department_assigned) {
            $data['ai_analysis'] = [
                'sentiment' => $this->sentiment ? [
                    'value' => $this->sentiment,
                    'label' => $this->sentiment->label(),
                    'color' => $this->sentiment->color(),
                ] : null,
                'urgency_level' => $this->urgency_level ? [
                    'value' => $this->urgency_level,
                    'label' => $this->urgency_level->label(),
                ] : null,
                'department_assigned' => $this->department_assigned,
                'topic_cluster' => $this->topic_cluster,
                'intent' => $this->intent,
            ];
        }

        // Add detailed AI analysis if relationship is loaded
        if ($this->relationLoaded('aIAnalysis') && $this->aIAnalysis) {
            $data['ai_analysis_details'] = [
                'summary' => $this->aIAnalysis->summary,
                'suggested_tags' => $this->aIAnalysis->suggested_tags,
                'department_suggestion' => $this->aIAnalysis->department_suggestion,
                'analysis_date' => $this->aIAnalysis->analysis_date->format('Y-m-d H:i:s'),
            ];
        }

        // Add admin data for admin users only
        if (auth()->check() && auth()->user()->role === 'admin') {
            $data['admin'] = [
                'admin_notes' => $this->admin_notes ?? null,
            ];
        }

        return $data;
    }
}
