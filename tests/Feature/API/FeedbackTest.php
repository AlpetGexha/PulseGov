<?php

declare(strict_types=1);

use App\Enum\FeedbackStatus;
use App\Models\Feedback;
use App\Models\User;

test('authenticated user can submit feedback', function () {
    // Create a user
    $user = User::factory()->create();

    // Act as this user
    $this->actingAs($user);

    // Submit feedback
    $response = $this->postJson('/api/feedback', [
        'title' => 'Test Feedback',
        'body' => 'This is a test feedback submission.',
        'location' => 'Test Location',
        'service' => 'Test Service',
    ]);

    // Assert response status and structure
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'body',
                'tracking_code',
                'status',
                'created_at',
                'updated_at',
            ],
            'message',
        ]);

    // Verify data was stored correctly
    $this->assertDatabaseHas('feedback', [
        'user_id' => $user->id,
        'title' => 'Test Feedback',
        'body' => 'This is a test feedback submission.',
        'status' => FeedbackStatus::UNDER_REVIEW->value,
    ]);
});

test('authenticated user can view their feedback', function () {
    // Create a user
    $user = User::factory()->create();

    // Create some feedback for this user
    $feedback = Feedback::factory()->create([
        'user_id' => $user->id,
        'title' => 'My Feedback',
        'body' => 'My feedback details',
    ]);

    // Act as this user
    $this->actingAs($user);

    // Get the feedback
    $response = $this->getJson("/api/feedback/{$feedback->id}");

    // Assert response
    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'My Feedback')
        ->assertJsonPath('data.body', 'My feedback details');
});

test('user cannot view other users feedback unless admin', function () {
    // Create two users
    $user1 = User::factory()->create(['role' => 'citizen']);
    $user2 = User::factory()->create(['role' => 'citizen']);

    // Create feedback for user1
    $feedback = Feedback::factory()->create([
        'user_id' => $user1->id,
    ]);

    // Act as user2
    $this->actingAs($user2);

    // Try to get user1's feedback
    $response = $this->getJson("/api/feedback/{$feedback->id}");

    // Should get forbidden
    $response->assertStatus(403);
});
