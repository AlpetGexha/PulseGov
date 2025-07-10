<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class GjakovaFeedbackSeeder extends Seeder
{
    public function run()
    {
        // Gjakova city center approximate coordinates
        $centerLat = 42.3803;
        $centerLng = 20.4308;

        // Sample feedback data for Gjakova
        $feedbackData = [
            [
                'title' => 'Road Maintenance Needed',
                'body' => 'The road near city center has several potholes that need immediate attention.',
                'location' => 'Gjakova City Center',
                'department_assigned' => 'Public Works',
                'feedback_type' => FeedbackType::PROBLEM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::HIGH,
                'lat_offset' => 0.002,
                'lng_offset' => 0.001,
            ],
            [
                'title' => 'Park Improvements',
                'body' => 'The central park needs more benches and better lighting for evening safety.',
                'location' => 'Gjakova Central Park',
                'department_assigned' => 'Parks and Recreation',
                'feedback_type' => FeedbackType::SUGGESTION,
                'sentiment' => FeedbackSentiment::NEUTRAL,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'lat_offset' => -0.001,
                'lng_offset' => 0.002,
            ],
            [
                'title' => 'Street Light Outage',
                'body' => 'Several street lights are not working in the residential area.',
                'location' => 'Gjakova Residential Area',
                'department_assigned' => 'Public Works',
                'feedback_type' => FeedbackType::PROBLEM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::HIGH,
                'lat_offset' => 0.001,
                'lng_offset' => -0.001,
            ],
            [
                'title' => 'New Library Services',
                'body' => 'Great new services at the city library! The staff is very helpful.',
                'location' => 'Gjakova Public Library',
                'department_assigned' => 'Education',
                'feedback_type' => FeedbackType::PRAISE,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'urgency_level' => UrgencyLevel::LOW,
                'lat_offset' => -0.002,
                'lng_offset' => -0.002,
            ],
            [
                'title' => 'Traffic Signal Malfunction',
                'body' => 'The traffic light at the main intersection is not working properly.',
                'location' => 'Gjakova Main Intersection',
                'department_assigned' => 'Transportation',
                'feedback_type' => FeedbackType::PROBLEM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::CRITICAL,
                'lat_offset' => 0.001,
                'lng_offset' => 0.003,
            ],
        ];

        // Get a user for the feedback
        $user = User::first() ?? User::factory()->create();

        foreach ($feedbackData as $data) {
            $latitude = $centerLat + $data['lat_offset'];
            $longitude = $centerLng + $data['lng_offset'];

            Feedback::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'location' => $data['location'],
                'department_assigned' => $data['department_assigned'],
                'feedback_type' => $data['feedback_type'],
                'sentiment' => $data['sentiment'],
                'urgency_level' => $data['urgency_level'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => $data['location'] . ', Gjakova, Kosovo',
                'created_at' => now()->subHours(rand(1, 72)),
            ]);
        }
    }
}
