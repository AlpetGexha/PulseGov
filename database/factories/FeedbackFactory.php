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
            'service' => fake()->regexify('[A-Za-z0-9]{255}'),
            'message' => fake()->text(),
            'rating' => fake()->numberBetween(1, 5),
            'sentiment' => fake()->randomElement(FeedbackSentiment::values()),
            'status' => fake()->randomElement(FeedbackStatus::values()),
            'feedback_type' => fake()->randomElement(FeedbackType::values()),
            'tracking_code' => fake()->regexify('[A-Za-z0-9]{255}'),
            'urgency_level' => fake()->randomElement(UrgencyLevel::values()),
            'intent' => fake()->regexify('[A-Za-z0-9]{255}'),
            'topic_cluster' => fake()->regexify('[A-Za-z0-9]{255}'),
            'department_assigned' => fake()->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
