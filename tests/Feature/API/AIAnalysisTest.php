<?php

declare(strict_types=1);

use App\Actions\AnalyzeFeedback;
use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Jobs\ProcessFeedbackAIAnalysis;
use App\Models\AIAnalysis;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Mockery;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

beforeEach(function () {
    // Create admin user
    $this->adminUser = User::factory()->create([
        'role' => 'admin',
    ]);

    // Create regular user
    $this->user = User::factory()->create([
        'role' => 'citizen',
    ]);

    // Create a feedback item
    $this->feedback = Feedback::factory()->create([
        'user_id' => $this->user->id,
        'body' => 'I think the local park needs more benches and better lighting.',
    ]);
});

test('admin can trigger AI analysis of feedback', function () {
    Queue::fake();

    // Act as admin
    $this->actingAs($this->adminUser);

    // Trigger analysis
    $response = $this->postJson("/api/feedback/{$this->feedback->id}/analyze");

    // Assert response
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Feedback analysis has been queued',
        ]);

    // Assert job was dispatched
    Queue::assertPushed(ProcessFeedbackAIAnalysis::class, function ($job) {
        return $job->feedback->id === $this->feedback->id;
    });
});

test('regular user cannot trigger AI analysis of feedback', function () {
    Queue::fake();

    // Act as regular user
    $this->actingAs($this->user);

    // Try to trigger analysis
    $response = $this->postJson("/api/feedback/{$this->feedback->id}/analyze");

    // Assert unauthorized
    $response->assertStatus(403);

    // Assert job was not dispatched
    Queue::assertNotPushed(ProcessFeedbackAIAnalysis::class);
});

test('feedback analysis job processes correctly', function () {
    // Mock OpenAI client
    $mockResponse = [
        'sentiment' => 'positive',
        'urgency_level' => 'medium',
        'feedback_type' => 'suggestion',
        'tags' => ['park', 'benches', 'lighting', 'safety'],
        'department' => 'Parks and Recreation',
        'summary' => 'User suggests adding more benches and better lighting in the local park.',
    ];

    // Create a mock for the OpenAI service
    $this->mock(OpenAI::class, function ($mock) use ($mockResponse) {
        $chatMock = Mockery::mock();
        $mock->shouldReceive('chat')->andReturn($chatMock);

        $responseMock = Mockery::mock(CreateResponse::class);
        $choiceMock = (object) [
            'message' => (object) [
                'content' => json_encode($mockResponse),
            ],
        ];
        $responseMock->choices = [(object) ['choice' => $choiceMock]];

        $chatMock->shouldReceive('create')->andReturn($responseMock);
    });

    // Use the analyzer directly
    $analyzer = app(AnalyzeFeedback::class);
    $analysis = $analyzer->handle($this->feedback);

    // Refresh from database
    $this->feedback->refresh();

    // Assert AIAnalysis record was created
    $this->assertInstanceOf(AIAnalysis::class, $analysis);
    $this->assertEquals($this->feedback->id, $analysis->feedback_id);

    // Assert feedback was updated with analysis data
    $this->assertEquals(FeedbackSentiment::POSITIVE, $this->feedback->sentiment);
    $this->assertEquals(UrgencyLevel::MEDIUM, $this->feedback->urgency_level);
    $this->assertEquals(FeedbackType::SUGGESTION, $this->feedback->feedback_type);
    $this->assertEquals('Parks and Recreation', $this->feedback->department_assigned);
});

test('ai analysis data is included in feedback response', function () {
    // Create AI analysis for our feedback
    $analysis = AIAnalysis::create([
        'feedback_id' => $this->feedback->id,
        'sentiment' => FeedbackSentiment::POSITIVE,
        'suggested_tags' => ['park', 'benches', 'lighting'],
        'summary' => 'User suggests adding more benches and better lighting in the local park.',
        'department_suggestion' => 'Parks and Recreation',
        'analysis_date' => now(),
    ]);

    // Update feedback with the analysis insights
    $this->feedback->update([
        'sentiment' => FeedbackSentiment::POSITIVE,
        'feedback_type' => FeedbackType::SUGGESTION,
        'urgency_level' => UrgencyLevel::MEDIUM,
        'department_assigned' => 'Parks and Recreation',
    ]);

    // Act as admin
    $this->actingAs($this->adminUser);

    // Get feedback details
    $response = $this->getJson("/api/feedback/{$this->feedback->id}");

    // Assert response includes AI analysis data
    $response->assertStatus(200)
        ->assertJsonPath('data.ai_analysis.sentiment.value', FeedbackSentiment::POSITIVE->value)
        ->assertJsonPath('data.ai_analysis.department_assigned', 'Parks and Recreation')
        ->assertJsonStructure([
            'data' => [
                'ai_analysis' => [
                    'sentiment' => [
                        'value', 'label', 'color',
                    ],
                    'urgency_level',
                    'department_assigned',
                ],
                'ai_analysis_details' => [
                    'summary',
                    'suggested_tags',
                    'department_suggestion',
                    'analysis_date',
                ],
            ],
        ]);
});
