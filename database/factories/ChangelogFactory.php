<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Changelog;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChangelogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Changelog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->text(),
            'created_at' => fake()->dateTime(),
        ];
    }
}
