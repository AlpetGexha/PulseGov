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
            'sentiment' => fake()->randomElement(FeedbackSentiment::values()),
            'suggested_tags' => fake()->text(),
            'analysis_date' => fake()->dateTime(),
        ];
    }
}
