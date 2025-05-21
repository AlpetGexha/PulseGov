<?php

namespace Database\Factories;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Feedback;
use App\Models\User;

class FeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feedback::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'service' => fake()->word(),
            'message' => fake()->text(), // For backward compatibility
            'location' => fake()->city(),
            'rating' => fake()->numberBetween(1, 5),
            'sentiment' => fake()->randomElement(FeedbackSentiment::cases()),
            'status' => fake()->randomElement(FeedbackStatus::cases()),
            'feedback_type' => fake()->randomElement(FeedbackType::cases()),
            'tracking_code' => strtoupper(Str::random(8)),
            'urgency_level' => fake()->randomElement(UrgencyLevel::cases()),
            'intent' => fake()->word(),
            'topic_cluster' => fake()->word(),
            'department_assigned' => fake()->randomElement(['Parks and Recreation', 'Public Works', 'Transportation', 'Health Services', 'Education']),
        ];
    }

    /**
     * Factory state for unprocessed feedback.
     */
    public function unprocessed(): static
    {
        return $this->state(fn (array $attributes) => [
            'sentiment' => null,
            'urgency_level' => null,
            'department_assigned' => null,
            'topic_cluster' => null,
            'intent' => null,
        ]);
    }
}
