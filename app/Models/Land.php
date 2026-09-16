<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Land extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'kode_lahan',
        'nama_lahan',
        'desa',
        'kecamatan',
        'kabupaten',
        'latitude',
        'longitude',
        'polygon_geojson',
        'luas',
        'luas_m2',
        'jenis_tanaman',
        'tanggal_tanam',
        'jenis_tanah',
        'status',
        'catatan',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'polygon_geojson' => 'array',
        'tanggal_tanam' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'luas' => 'float',
        'luas_m2' => 'float',
    ];

    public function rainfallRecords(): HasMany
    {
        return $this->hasMany(RainfallRecord::class);
    }

    public function weatherForecasts(): HasMany
    {
        return $this->hasMany(WeatherForecast::class);
    }

    public function satelliteObservations(): HasMany
    {
        return $this->hasMany(SatelliteObservation::class);
    }

    public function waterBalances(): HasMany
    {
        return $this->hasMany(WaterBalance::class);
    }

    public function droughtAnalyses(): HasMany
    {
        return $this->hasMany(DroughtAnalysis::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * Umur tanaman dalam hari sejak tanggal tanam.
     */
    public function getUmurTanamanAttribute(): int
    {
        return (int) $this->tanggal_tanam->diffInDays(now());
    }

    /**
     * Observasi satelit terbaru.
     */
    public function latestObservation(): ?SatelliteObservation
    {
        return $this->satelliteObservations()
            ->orderByDesc('observation_date')
            ->first();
    }

    /**
     * Analisis kekeringan terbaru.
     */
    public function latestDroughtAnalysis(): ?DroughtAnalysis
    {
        return $this->droughtAnalyses()
            ->orderByDesc('analysis_date')
            ->first();
    }

    /**
     * Rekomendasi terbaru.
     */
    public function latestRecommendation(): ?Recommendation
    {
        return $this->recommendations()
            ->orderByDesc('recommendation_date')
            ->first();
    }
}
