<?php

use App\Enum\FeedbackStatus as FeedbackStatusEnum;
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
        Schema::create('feedback_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Feedback::class)->constrained();
            $table->string('status')->default(FeedbackStatusEnum::UNDER_REVIEW->value);
            $table->string('admin_id');
            $table->text('comment');
            $table->timestamp('changed_at');
            $table->foreignIdFor(User::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_statuses');
    }
};
