<?php

declare(strict_types=1);

use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('title', 255);
            $table->text('body');
            $table->string('location', 255)->nullable();
            $table->string('tracking_code', length: 255)->nullable();
            $table->string('service', 255)->nullable();
            // $table->integer('rating');
            $table->boolean('is_public')->default(false);
            $table->string('sentiment')->nullable()->default(null);
            $table->string('status')->nullable()->default(FeedbackStatus::UNDER_REVIEW->value);
            $table->string('feedback_type')->nullable()->default(FeedbackType::SUGGESTION->value);
            $table->string('urgency_level')->nullable()->default(null);
            $table->string('intent', 255)->nullable();
            $table->string('topic_cluster', 255)->nullable();
            $table->string('department_assigned', 255)->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
