<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIModelMetrics extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ai_analysis_id',
        'accuracy',
        'processing_time',
        'status',
        'a_i_analysis_id',
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
            'accuracy' => 'decimal',
            'processing_time' => 'decimal',
            'a_i_analysis_id' => 'integer',
        ];
    }

    public function aIAnalysis(): BelongsTo
    {
        return $this->belongsTo(AIAnalysis::class);
    }
}
