<?php

use App\Models\AIAnalysis;
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
        Schema::create('a_i_model_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AIAnalysis::class)->constrained();
            $table->decimal('accuracy')->nullable();
            $table->decimal('processing_time')->nullable();
            $table->string('status', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_i_model_metrics');
    }
};
