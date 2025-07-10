<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Conversation extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'context_data',
        'token_usage',
        'last_activity_at',
        'is_active',
    ];

    protected $casts = [
        'context_data' => 'array',
        'last_activity_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function addTokenUsage(int $tokens): void
    {
        $this->increment('token_usage', $tokens);
    }

    public function generateTitle(): string
    {
        $userMessage = $this->messages()->where('role', 'user')->first();
        if ($userMessage) {
            return 'New Conversation ' . $this->created_at->format('M j, Y g:i A');
        }

        return 'New Conversation ' . $this->created_at->format('M j, Y g:i A');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
