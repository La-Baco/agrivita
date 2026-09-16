<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'land_id',
        'recommendation_date',
        'priority_level',
        'priority_score',
        'rationale',
        'recommendation_text',
        'water_deficit_mm',
        'water_quota_estimate_mm',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'recommendation_date' => 'date',
        'priority_score' => 'float',
        'water_deficit_mm' => 'float',
        'water_quota_estimate_mm' => 'float',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority_level) {
            'TINGGI' => 'badge-danger',
            'SEDANG' => 'badge-warning',
            'RENDAH' => 'badge-success',
            default => 'badge-secondary',
        };
    }
}
