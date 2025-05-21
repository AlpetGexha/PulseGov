<?php

namespace App\Models;

use App\Enum\FeedbackSentiment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIAnalysis extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'feedback_id',
        'sentiment',
        'suggested_tags',
        'analysis_date',
        'summary',
        'department_suggestion',
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
            'feedback_id' => 'integer',
            'sentiment' => FeedbackSentiment::class,
            'analysis_date' => 'timestamp',
            'suggested_tags' => 'array',
        ];
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }
}
