<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatelliteObservation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'land_id',
        'observation_date',
        'image_url',
        'cloud_percentage',
        'b4_red',
        'b8_nir',
        'ndvi',
        'source',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'observation_date' => 'date',
        'cloud_percentage' => 'float',
        'b4_red' => 'float',
        'b8_nir' => 'float',
        'ndvi' => 'float',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    /**
     * Label kondisi vegetasi berdasarkan nilai NDVI.
     */
    public function getVegetationStatusAttribute(): string
    {
        if ($this->ndvi >= 0.50) {
            return 'Baik';
        } elseif ($this->ndvi >= 0.30) {
            return 'Sedang';
        } else {
            return 'Kurang Baik';
        }
    }

    /**
     * Warna badge kondisi vegetasi.
     */
    public function getVegetationColorAttribute(): string
    {
        if ($this->ndvi >= 0.50) {
            return 'green';
        } elseif ($this->ndvi >= 0.30) {
            return 'yellow';
        } else {
            return 'red';
        }
    }
}
