<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['user', 'aIAnalysis'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'title' => $feedback->title,
                    'body' => $feedback->body,
                    'location' => $feedback->location,
                    'latitude' => (float)$feedback->latitude,
                    'longitude' => (float)$feedback->longitude,
                    'sentiment' => $feedback->sentiment?->value,
                    'urgency_level' => $feedback->urgency_level?->value,
                    'department_assigned' => $feedback->department_assigned,
                    'status' => $feedback->status?->value,
                    // 'category' => $feedback->category?->value,
                    'image_url' => "https://media.istockphoto.com/id/533964313/photo/road-damage-pot-hole.webp?s=2048x2048&w=is&k=20&c=9P45uDRngd4WWSm5CpVbBO5DPE0vDTHecOtr7zRt73Q=",
                    'created_at' => $feedback->created_at->format('Y-m-d H:i:s'),
                    'user' => [
                        'name' => $feedback->user?->name,
                        // 'avatar' => $feedback->user?->profile_photo_url,
                    ],
                    'analysis' => [
                        'summary' => $feedback->aIAnalysis?->summary,
                        'keywords' => $feedback->aIAnalysis?->keywords,
                    ],
                ];
            });

        return Inertia::render('Map/Index', [
            'feedbacks' => $feedbacks
        ]);
    }
}
