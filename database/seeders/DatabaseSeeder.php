<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $users = User::factory()->count(30)->create(); // Increased from 15 to 30

//User::factory(30)->create();
        // Run the feedback seeder
        $this->call([
            FeedbackSeeder::class,
        ]);
    }
}
