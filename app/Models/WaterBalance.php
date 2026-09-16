<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterBalance extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'land_id',
        'calculation_date',
        'initial_water',
        'effective_rainfall',
        'irrigation',
        'crop_water_use',
        'water_loss',
        'final_water_balance',
        'deficit_surplus',
        'details_json',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'calculation_date' => 'date',
        'initial_water' => 'float',
        'effective_rainfall' => 'float',
        'irrigation' => 'float',
        'crop_water_use' => 'float',
        'water_loss' => 'float',
        'final_water_balance' => 'float',
        'deficit_surplus' => 'float',
        'details_json' => 'array',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }

    /**
     * Apakah kondisi defisit air.
     */
    public function isDeficit(): bool
    {
        return $this->final_water_balance < 0;
    }
}
