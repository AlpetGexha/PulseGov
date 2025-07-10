<?php

declare(strict_types=1);

use App\Enum\FeedbackSentiment;
use App\Models\Feedback;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('a_i_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Feedback::class)->constrained();
            $table->string('sentiment')->default(FeedbackSentiment::NEUTRAL->value);
            $table->text('suggested_tags');
            $table->text('summary')->nullable();
            $table->string('department_suggestion')->nullable();
            $table->timestamp('analysis_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('a_i_analyses');
    }
};
