<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DroughtAnalysis;
use App\Models\Land;
use App\Models\Recommendation;
use App\Services\NdviService;
use App\Services\RainfallService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;

class LandApiController extends Controller
{
    public function __construct(
        private readonly NdviService $ndviService,
        private readonly RainfallService $rainfallService,
        private readonly WeatherService $weatherService,
    ) {}

    /**
     * Semua lahan dengan status terbaru (untuk peta Leaflet).
     */
    public function index(): JsonResponse
    {
        $lands = Land::with(['droughtAnalyses' => function ($q) {
            $q->latest('analysis_date')->limit(1);
        }])->get();

        $features = $lands->map(function (Land $land) {
            $drought = $land->droughtAnalyses->first();

            return [
                'type' => 'Feature',
                'geometry' => $land->polygon_geojson,
                'properties' => [
                    'id' => $land->id,
                    'kode_lahan' => $land->kode_lahan,
                    'nama_lahan' => $land->nama_lahan,
                    'desa' => $land->desa,
                    'kecamatan' => $land->kecamatan,
                    'jenis_tanaman' => $land->jenis_tanaman,
                    'luas' => $land->luas,
                    'umur_tanaman' => $land->umur_tanaman,
                    'status' => $land->status,
                    'drought_status' => $drought?->status ?? 'HIJAU',
                    'drought_label' => $drought?->status_label ?? 'Data Belum Tersedia',
                    'drought_color' => DroughtAnalysis::$statusColors[$drought?->status ?? 'HIJAU'],
                    'ndvi' => $drought?->ndvi_value,
                    'water_balance' => $drought?->water_balance_value,
                    'url' => route('lands.show', $land->id),
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * Detail satu lahan.
     */
    public function show(Land $land): JsonResponse
    {
        $drought = $land->latestDroughtAnalysis();
        $observation = $land->latestObservation();
        $recommendation = $land->latestRecommendation();
        $ndviData = $this->ndviService->getLatestNdviWithChange($land);
        $rain3d = $this->weatherService->getForecastRainfall3Days($land);

        return response()->json([
            'land' => $land,
            'drought_status' => $drought,
            'ndvi' => [
                'value' => $observation?->ndvi,
                'change_pct' => $ndviData['change_pct'],
                'classification' => $ndviData['classification'],
            ],
            'water_balance' => $land->waterBalances()->latest('calculation_date')->first(),
            'recommendation' => $recommendation,
            'forecast_rain_3d_mm' => $rain3d,
        ]);
    }

    /**
     * NDVI history untuk grafik.
     */
    public function ndvi(Land $land): JsonResponse
    {
        $observations = $land->satelliteObservations()
            ->orderBy('observation_date')
            ->get()
            ->map(fn ($obs) => [
                'date' => $obs->observation_date->format('Y-m-d'),
                'ndvi' => $obs->ndvi,
                'b4_red' => $obs->b4_red,
                'b8_nir' => $obs->b8_nir,
                'source' => $obs->source,
            ]);

        return response()->json(['data' => $observations]);
    }

    /**
     * Data curah hujan untuk grafik.
     */
    public function rainfall(Land $land): JsonResponse
    {
        $records = $land->rainfallRecords()
            ->orderBy('observation_date')
            ->limit(30)
            ->get()
            ->map(fn ($r) => [
                'date' => $r->observation_date->format('Y-m-d'),
                'rainfall_mm' => $r->rainfall_mm,
                'source' => $r->source,
            ]);

        return response()->json([
            'data' => $records,
            'is_demo' => $this->rainfallService->isDemo(),
        ]);
    }

    /**
     * Neraca air history untuk grafik.
     */
    public function waterBalance(Land $land): JsonResponse
    {
        $balances = $land->waterBalances()
            ->orderBy('calculation_date')
            ->limit(30)
            ->get()
            ->map(fn ($wb) => [
                'date' => $wb->calculation_date->format('Y-m-d'),
                'final_balance' => $wb->final_water_balance,
                'effective_rain' => $wb->effective_rainfall,
                'etc' => $wb->crop_water_use,
                'deficit_surplus' => $wb->deficit_surplus,
            ]);

        return response()->json(['data' => $balances]);
    }

    /**
     * Daftar prioritas semua lahan.
     */
    public function priority(): JsonResponse
    {
        $priorityOrder = ['TINGGI' => 0, 'SEDANG' => 1, 'RENDAH' => 2];

        $recs = Recommendation::with('land')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')->from('recommendations')->groupBy('land_id');
            })
            ->get()
            ->sortBy(fn ($r) => $priorityOrder[$r->priority_level] ?? 3)
            ->values()
            ->map(fn ($r) => [
                'land_id' => $r->land_id,
                'nama_lahan' => $r->land->nama_lahan,
                'kecamatan' => $r->land->kecamatan,
                'priority_level' => $r->priority_level,
                'priority_score' => $r->priority_score,
                'water_deficit_mm' => $r->water_deficit_mm,
                'rationale' => $r->rationale,
            ]);

        return response()->json(['data' => $recs]);
    }
}
