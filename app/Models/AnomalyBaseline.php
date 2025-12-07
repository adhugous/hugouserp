<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnomalyBaseline extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'metric_key',
        'entity_type',
        'entity_id',
        'mean',
        'std_dev',
        'min',
        'max',
        'sample_count',
        'period_start',
        'period_end',
        'metadata',
    ];

    protected $casts = [
        'mean' => 'decimal:2',
        'std_dev' => 'decimal:2',
        'min' => 'decimal:2',
        'max' => 'decimal:2',
        'sample_count' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the related entity.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if value is an anomaly.
     */
    public function isAnomaly(float $value, float $stdDevThreshold = 2.0): bool
    {
        $upperBound = $this->mean + ($this->std_dev * $stdDevThreshold);
        $lowerBound = $this->mean - ($this->std_dev * $stdDevThreshold);

        return $value > $upperBound || $value < $lowerBound;
    }

    /**
     * Calculate z-score for value.
     */
    public function getZScore(float $value): float
    {
        if ($this->std_dev == 0) {
            return 0;
        }

        return ($value - $this->mean) / $this->std_dev;
    }

    /**
     * Get anomaly severity.
     */
    public function getAnomalySeverity(float $value): ?string
    {
        $zScore = abs($this->getZScore($value));

        if ($zScore < 2.0) {
            return null; // Not an anomaly
        }

        if ($zScore >= 3.0) {
            return 'critical';
        }

        if ($zScore >= 2.5) {
            return 'warning';
        }

        return 'info';
    }
}
