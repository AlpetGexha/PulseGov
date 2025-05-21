<?php

use App\Enum\FeedbackSentiment;
use App\Models\Feedback;
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
        Schema::create('a_i_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Feedback::class)->constrained();
            $table->string('sentiment')->default(FeedbackSentiment::NEUTRAL->value);
            $table->text('suggested_tags');
            $table->timestamp('analysis_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_i_analyses');
    }
};
