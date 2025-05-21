<?php

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
            $table->date('date');
            $table->string('model_name');
            $table->decimal('avg_processing_time', 10, 2)->default(0);
            $table->integer('analyses_count')->default(0);
            $table->decimal('coverage_percentage', 5, 2)->default(0);
            $table->decimal('accuracy_score', 5, 2)->nullable();
            $table->decimal('cost', 10, 4)->nullable();
            $table->integer('tokens_used')->nullable();
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
