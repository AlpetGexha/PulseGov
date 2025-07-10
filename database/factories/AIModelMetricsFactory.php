<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AIModelMetrics;
use Illuminate\Database\Eloquent\Factories\Factory;

final class AIModelMetricsFactory extends Factory
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
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'model_name' => fake()->randomElement(['gpt-4o', 'gpt-3.5-turbo', 'claude-3-haiku']),
            'avg_processing_time' => fake()->randomFloat(2, 0.5, 5),
            'analyses_count' => fake()->numberBetween(10, 500),
            'coverage_percentage' => fake()->randomFloat(2, 50, 100),
            'accuracy_score' => fake()->randomFloat(2, 70, 99),
            'cost' => fake()->randomFloat(4, 0.01, 10),
            'tokens_used' => fake()->numberBetween(500, 10000),
        ];
    }
}
