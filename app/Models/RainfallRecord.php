<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RainfallRecord extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'land_id',
        'observation_date',
        'rainfall_mm',
        'source',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'observation_date' => 'date',
        'rainfall_mm' => 'float',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(Land::class);
    }
}
