<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

final class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'password' => Hash::make('password'), // Use a fixed password for testing
            'role' => fake()->randomElement(UserRole::values()),
        ];
    }
}
