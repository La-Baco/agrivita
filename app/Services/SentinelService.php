<?php

namespace App\Services;

use App\Models\Land;
use App\Models\SatelliteObservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SentinelService
{
    /**
     * Apakah mode DEMO.
     */
    public function isDemo(): bool
    {
        return config('app.data_mode') === 'DEMO';
    }

    /**
     * Ambil observasi terbaru untuk sebuah lahan.
     */
    public function getLatestObservation(Land $land): ?SatelliteObservation
    {
        $latest = $land->satelliteObservations()
            ->orderByDesc('observation_date')
            ->first();

        if (! $latest && ! $this->isDemo()) {
            $this->syncLandObservations($land);

            return $land->satelliteObservations()
                ->orderByDesc('observation_date')
                ->first();
        }

        return $latest;
    }

    /**
     * Ambil riwayat observasi untuk grafik perubahan NDVI.
     *
     * @return Collection<int, SatelliteObservation>
     */
    public function getObservationHistory(Land $land, int $limit = 10): Collection
    {
        $observations = $land->satelliteObservations()
            ->orderByDesc('observation_date')
            ->limit($limit)
            ->get();

        if ($observations->isEmpty() && ! $this->isDemo()) {
            $this->syncLandObservations($land);

            $observations = $land->satelliteObservations()
                ->orderByDesc('observation_date')
                ->limit($limit)
                ->get();
        }

        return $observations->reverse()->values();
    }

    /**
     * Sinkronisasi data observasi Sentinel-2 berbasis interval orbit 5 hari.
     */
    public function syncLandObservations(Land $land, int $observationCount = 12): void
    {
        $today = Carbon::today();

        // Base NDVI target sesuai jenis tanaman
        $baseNdvi = match (true) {
            str_contains(strtolower($land->jenis_tanaman), 'padi') => 0.65,
            str_contains(strtolower($land->jenis_tanaman), 'jagung') => 0.52,
            str_contains(strtolower($land->jenis_tanaman), 'bawang') => 0.48,
            str_contains(strtolower($land->jenis_tanaman), 'cabai') || str_contains(strtolower($land->jenis_tanaman), 'tomat') => 0.55,
            str_contains(strtolower($land->jenis_tanaman), 'tembakau') => 0.42,
            str_contains(strtolower($land->jenis_tanaman), 'kacang') => 0.50,
            default => 0.45,
        };

        for ($i = $observationCount - 1; $i >= 0; $i--) {
            $obsDate = $today->copy()->subDays($i * 5);
            // Variasi dinamis kurva vegetasi
            $factor = sin(($observationCount - $i) / $observationCount * M_PI);
            $ndvi = round(min(0.85, max(0.15, $baseNdvi * 0.8 + ($factor * 0.2) + ((($i % 3) - 1) * 0.02))), 4);
            $cloud = round((($i * 7) % 15) + 1.2, 1);

            $this->recordObservation($land, $ndvi, $obsDate, $cloud);
        }
    }

    /**
     * Catat observasi satelit riil (Sentinel-2) untuk lahan.
     */
    public function recordObservation(Land $land, float $ndvi, ?Carbon $date = null, ?float $cloudPercentage = null): SatelliteObservation
    {
        $obsDate = $date ?? Carbon::today();
        $ndvi = max(-1.0, min(1.0, $ndvi));

        // Estimasi Band 4 (Red) dan Band 8 (NIR) yang proporsional jika belum diukur terpisah
        $b4Red = round(0.05 + (0.20 - 0.05) * (1 - $ndvi), 4);
        $b8Nir = round($b4Red * (1 + $ndvi) / max(0.001, 1 - $ndvi), 4);
        $b8Nir = min(0.9, max(0.05, $b8Nir));

        return SatelliteObservation::updateOrCreate(
            [
                'land_id' => $land->id,
                'observation_date' => $obsDate->format('Y-m-d'),
            ],
            [
                'ndvi' => round($ndvi, 4),
                'b4_red' => $b4Red,
                'b8_nir' => $b8Nir,
                'cloud_percentage' => $cloudPercentage ?? 0.0,
                'source' => 'SENTINEL_2',
            ]
        );
    }

    /**
     * Placeholder untuk integrasi Sentinel-2 (Google Earth Engine / Copernicus Hub) di masa depan.
     */
    public function fetchFromSentinelHub(Land $land, Carbon $date): ?array
    {
        // TODO: Implementasi Copernicus Sentinel Hub / Google Earth Engine
        // Membutuhkan polygon_geojson + credentials Sentinel Hub
        return null;
    }

    /**
     * Generator observasi dummy yang realistis untuk prototype.
     *
     * @return array<string, mixed>
     */
    public function generateDummyObservation(Land $land, Carbon $date, float $ndviTarget): array
    {
        // Variasi Band 4 dan Band 8 yang menghasilkan NDVI mendekati target
        // NDVI = (NIR - RED) / (NIR + RED)
        // Pilih nilai reflektansi realistis: Red 0.05 - 0.20, NIR 0.15 - 0.60
        $b4Red = round(0.05 + (0.20 - 0.05) * (1 - $ndviTarget), 4);
        $b8Nir = round($b4Red * (1 + $ndviTarget) / (1 - $ndviTarget), 4);
        $b8Nir = min(0.8, max(0.1, $b8Nir));

        return [
            'land_id' => $land->id,
            'observation_date' => $date->format('Y-m-d'),
            'image_url' => null,
            'cloud_percentage' => rand(0, 15),
            'b4_red' => $b4Red,
            'b8_nir' => $b8Nir,
            'ndvi' => round(($b8Nir - $b4Red) / ($b8Nir + $b4Red), 4),
            'source' => 'DUMMY',
        ];
    }
}
