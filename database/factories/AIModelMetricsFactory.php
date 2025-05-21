<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AIAnalysis;
use App\Models\AIModelMetrics;

class AIModelMetricsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AIModelMetrics::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ai_analysis_id' => fake()->word(),
            'accuracy' => fake()->randomFloat(0, 0, 9999999999.),
            'processing_time' => fake()->randomFloat(0, 0, 9999999999.),
            'status' => fake()->regexify('[A-Za-z0-9]{255}'),
            'a_i_analysis_id' => AIAnalysis::factory(),
        ];
    }
}
