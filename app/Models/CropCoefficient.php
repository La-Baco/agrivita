<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropCoefficient extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'crop_name',
        'growth_stage',
        'stage_days_min',
        'stage_days_max',
        'kc',
        'description',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'stage_days_min' => 'integer',
        'stage_days_max' => 'integer',
        'kc' => 'float',
    ];

    /**
     * Cari nilai Kc berdasarkan jenis tanaman dan umur tanaman (hari).
     */
    public static function findKcForCrop(string $cropName, int $daysSincePlanting): ?self
    {
        return static::where('crop_name', $cropName)
            ->where('stage_days_min', '<=', $daysSincePlanting)
            ->where('stage_days_max', '>=', $daysSincePlanting)
            ->first();
    }
}
