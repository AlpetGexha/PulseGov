<?php

namespace App\Models;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus as FeedbackStatusEnum;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feedback extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'location',
        'service',
        'sentiment',
        'status',
        'feedback_type',
        'tracking_code',
        'urgency_level',
        'intent',
        'topic_cluster',
        'department_assigned',
        'is_public'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'sentiment' => FeedbackSentiment::class,
            'status' => FeedbackStatusEnum::class,
            // 'feedback_type' => FeedbackType::class,
            'urgency_level' => UrgencyLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedbackCategories(): HasMany
    {
        return $this->hasMany(FeedbackCategory::class);
    }

    public function feedbackStatuses(): HasMany
    {
        return $this->hasMany(FeedbackStatus::class);
    }

    public function aIAnalysis(): HasOne
    {
        return $this->hasOne(AIAnalysis::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeedbackComment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(FeedbackVote::class);
    }
}
