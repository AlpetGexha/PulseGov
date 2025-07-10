<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\FeedbackStatus as FeedbackStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FeedbackStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'feedback_id',
        'status',
        'admin_id',
        'comment',
        'changed_at',
        'user_id',
    ];

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
            'status' => FeedbackStatusEnum::class,
            'changed_at' => 'timestamp',
            'user_id' => 'integer',
        ];
    }
}
