<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\UrgencyLevel;
use App\Models\Feedback;
use Illuminate\Database\Seeder;

class SampleFeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $feedbackSamples = [
            [
                'title' => 'Large Pothole on Main Street',
                'body' => 'There is a dangerous pothole on Main Street near the school. Multiple vehicles have been damaged and it poses a risk to children walking to school.',
                'location' => 'Main Street, Downtown',
                'department_assigned' => 'Municipal Road Maintenance',
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::HIGH,
            ],
            [
                'title' => 'Excellent Park Renovation',
                'body' => 'The recent renovation of Central Park is fantastic! The new playground equipment is amazing and the walking paths are beautiful.',
                'location' => 'Central Park',
                'department_assigned' => 'Parks and Recreation',
                'sentiment' => FeedbackSentiment::POSITIVE,
                'urgency_level' => UrgencyLevel::LOW,
            ],
            [
                'title' => 'Street Light Outage',
                'body' => 'The street lights on Oak Avenue have been out for a week. This creates a safety hazard for pedestrians and drivers.',
                'location' => 'Oak Avenue',
                'department_assigned' => 'Public Works',
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::MEDIUM,
            ],
            [
                'title' => 'Traffic Congestion at School Zone',
                'body' => 'During school hours, there is severe traffic congestion at the intersection near Lincoln Elementary. We need better traffic management.',
                'location' => 'Lincoln Elementary School',
                'department_assigned' => 'Transportation',
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::HIGH,
            ],
            [
                'title' => 'Water Quality Concerns',
                'body' => 'Residents in the Westside neighborhood have reported strange taste and odor in their tap water. This needs immediate investigation.',
                'location' => 'Westside',
                'department_assigned' => 'Water Services',
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::CRITICAL,
            ],
            [
                'title' => 'Great Library Services',
                'body' => 'The public library has been doing an excellent job with their community programs. The new digital resources are very helpful.',
                'location' => 'Central Library',
                'department_assigned' => 'Library Services',
                'sentiment' => FeedbackSentiment::POSITIVE,
                'urgency_level' => UrgencyLevel::LOW,
            ],
            [
                'title' => 'Garbage Collection Delays',
                'body' => 'Garbage collection in our neighborhood has been delayed multiple times this month. This is causing hygiene issues.',
                'location' => 'Riverside District',
                'department_assigned' => 'Waste Management',
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::MEDIUM,
            ],
            [
                'title' => 'Need More Bike Lanes',
                'body' => 'Our city needs more bike lanes to promote sustainable transportation. Current infrastructure is insufficient.',
                'location' => 'Downtown',
                'department_assigned' => 'Transportation',
                'sentiment' => FeedbackSentiment::NEUTRAL,
                'urgency_level' => UrgencyLevel::MEDIUM,
            ],
            [
                'title' => 'Public Safety Concerns',
                'body' => 'There have been several incidents of vandalism and theft in the downtown area. We need increased police presence.',
                'location' => 'Downtown',
                'department_assigned' => 'Public Safety',
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'urgency_level' => UrgencyLevel::HIGH,
            ],
            [
                'title' => 'Community Center Programming',
                'body' => 'The community center offers great programs for seniors and families. Would love to see more arts and crafts classes.',
                'location' => 'Community Center',
                'department_assigned' => 'Community Services',
                'sentiment' => FeedbackSentiment::POSITIVE,
                'urgency_level' => UrgencyLevel::LOW,
            ],
        ];

        foreach ($feedbackSamples as $sample) {
            Feedback::create(array_merge($sample, [
                'user_id' => 1,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]));
        }

        $this->command->info('Sample feedback data seeded successfully!');
    }
}
