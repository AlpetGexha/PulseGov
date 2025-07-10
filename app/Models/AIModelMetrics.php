<?php

declare(strict_types=1);

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
        'date',
        'model_name',
        'avg_processing_time',
        'analyses_count',
        'coverage_percentage',
        'accuracy_score',
        'cost',
        'tokens_used',
    ];

    public function aIAnalysis(): BelongsTo
    {
        return $this->belongsTo(AIAnalysis::class);
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
            'accuracy' => 'decimal',
            'processing_time' => 'decimal',
            'a_i_analysis_id' => 'integer',
        ];
    }
}
