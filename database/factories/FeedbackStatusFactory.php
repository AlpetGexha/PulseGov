<?php

namespace Database\Factories;

use App\Enum\FeedbackStatus as FeedbackStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Feedback;
use App\Models\FeedbackStatus;
use App\Models\User;

class FeedbackStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeedbackStatus::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'feedback_id' => Feedback::factory(),
            'status' => fake()->randomElement(FeedbackStatusEnum::values()),
            'admin_id' => fake()->word(),
            'comment' => fake()->text(),
            'changed_at' => fake()->dateTime(),
            'user_id' => User::factory(),
        ];
    }
}
