<?php

use App\Enum\VoteType;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedback_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Feedback::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('vote')->default(VoteType::UPVOTE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_votes');
    }
};
