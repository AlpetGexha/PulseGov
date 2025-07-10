<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\Conversation::class)->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->longText('content');
            $table->json('metadata')->nullable(); // Store additional data like token usage, processing time
            $table->integer('token_count')->default(0);
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
