<?php

declare(strict_types=1);

use App\Enum\UrgencyLevel;
use App\Models\AIAnalysis;
use App\Models\Feedback;
use App\Models\User;

beforeEach(function () {
    // Create admin user
    $this->adminUser = User::factory()->create([
        'role' => 'admin',
    ]);

    // Create regular user
    $this->user = User::factory()->create([
        'role' => 'citizen',
    ]);

    // Create some test feedback with AI analysis
    for ($i = 0; $i < 10; $i++) {
        $feedback = Feedback::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(rand(0, 29)), // Random date within last 30 days
        ]);

        // Add AI analysis for some of the feedback
        if ($i % 2 === 0) {
            AIAnalysis::factory()->create([
                'feedback_id' => $feedback->id,
            ]);
        }
    }

    // Create some critical feedback
    Feedback::factory()->create([
        'user_id' => $this->user->id,
        'urgency_level' => UrgencyLevel::CRITICAL,
    ]);
});

test('admin can view feedback stats dashboard', function () {
    // Act as admin
    $this->actingAs($this->adminUser);

    // Get dashboard stats
    $response = $this->getJson('/api/dashboard/feedback-stats');

    // Assert response
    $response->assertStatus(200)
        ->assertJsonStructure([
            'counts' => [
                'total',
                'analyzed',
                'pending',
            ],
            'byStatus',
            'byType',
            'bySentiment',
            'topDepartments',
            'trends',
            'topTags',
        ]);
});

test('admin can view recent activity', function () {
    // Act as admin
    $this->actingAs($this->adminUser);

    // Get recent activity
    $response = $this->getJson('/api/dashboard/recent-activity');

    // Assert response
    $response->assertStatus(200)
        ->assertJsonStructure([
            'recentFeedback' => [
                '*' => [
                    'id',
                    'title',
                    'body',
                    'tracking_code',
                    'created_at',
                    'status',
                ],
            ],
            'criticalFeedback',
            'counts',
        ]);

    // Check that we have critical feedback
    $this->assertNotEmpty($response->json('criticalFeedback'));
});

test('regular user cannot access dashboard endpoints', function () {
    // Act as regular user
    $this->actingAs($this->user);

    // Try to access dashboard endpoints
    $this->getJson('/api/dashboard/feedback-stats')->assertStatus(403);
    $this->getJson('/api/dashboard/recent-activity')->assertStatus(403);
});
