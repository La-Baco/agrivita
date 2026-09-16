<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherForecast extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'land_id',
        'forecast_datetime',
        'temperature',
        'humidity',
        'weather',
        'wind_speed',
        'wind_direction',
        'cloud_cover',
        'rainfall_estimate',
        'source',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'forecast_datetime' => 'datetime',
        'temperature' => 'float',
        'humidity' => 'float',
        'wind_speed' => 'float',
        'cloud_cover' => 'float',
        'rainfall_estimate' => 'float',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }
}
