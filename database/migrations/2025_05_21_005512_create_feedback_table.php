<?php

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('service', 255);
            $table->text('message');
            $table->integer('rating');
            $table->string('sentiment')->nullable()->default(null);
            $table->string('status')->default(FeedbackStatus::UNDER_REVIEW->value);
            $table->string('feedback_type')->default(FeedbackType::SUGGESTION->value);
            $table->string('tracking_code', 255);
            $table->string('urgency_level')->nullable()->default(null);
            $table->string('intent', 255)->nullable();
            $table->string('topic_cluster', 255)->nullable();
            $table->string('department_assigned', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
