<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enum\VoteType;
use App\Models\Feedback;
use App\Models\FeedbackVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class FeedbackVoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeedbackVote::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'feedback_id' => Feedback::factory(),
            'user_id' => User::factory(),
            'vote' => fake()->randomElement(VoteType::values()),
            'created_at' => fake()->dateTime(),
        ];
    }
}
