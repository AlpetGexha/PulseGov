<?php

namespace Database\Factories;

use App\Enum\FeedbackSentiment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AIAnalysis;
use App\Models\Feedback;

class AIAnalysisFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AIAnalysis::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'feedback_id' => Feedback::factory(),
            'sentiment' => fake()->randomElement(FeedbackSentiment::cases()),
            'suggested_tags' => json_encode(fake()->words(3)),
            'summary' => fake()->sentence(),
            'department_suggestion' => fake()->randomElement(['Parks and Recreation', 'Public Works', 'Transportation', 'Health Services', 'Education']),
            'analysis_date' => fake()->dateTime(),
        ];
    }
}
