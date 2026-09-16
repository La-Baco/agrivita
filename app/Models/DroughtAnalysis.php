<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DroughtAnalysis extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'land_id',
        'analysis_date',
        'water_balance_value',
        'rainfall_7d',
        'ndvi_value',
        'ndvi_change_pct',
        'forecast_rain_3d',
        'water_need_mm',
        'status',
        'status_label',
        'analysis_notes',
        'metrics_json',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'analysis_date' => 'date',
        'water_balance_value' => 'float',
        'rainfall_7d' => 'float',
        'ndvi_value' => 'float',
        'ndvi_change_pct' => 'float',
        'forecast_rain_3d' => 'float',
        'water_need_mm' => 'float',
        'metrics_json' => 'array',
    ];

    /** @var array<string, string> */
    public static array $statusColors = [
        'HIJAU' => '#22c55e',
        'KUNING' => '#eab308',
        'ORANYE' => '#f97316',
        'MERAH' => '#ef4444',
    ];

    /** @var array<string, string> */
    public static array $statusLabels = [
        'HIJAU' => 'Air Cukup',
        'KUNING' => 'Defisit Ringan',
        'ORANYE' => 'Risiko Kekeringan',
        'MERAH' => 'Prioritas Air',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    public function getStatusColorHexAttribute(): string
    {
        return static::$statusColors[$this->status] ?? '#6b7280';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'HIJAU' => 'badge-success',
            'KUNING' => 'badge-warning',
            'ORANYE' => 'badge-orange',
            'MERAH' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
