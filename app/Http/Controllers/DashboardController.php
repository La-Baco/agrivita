<?php

namespace App\Http\Controllers;

use App\Models\DroughtAnalysis;
use App\Models\Land;
use App\Models\RainfallRecord;
use App\Models\Recommendation;
use App\Models\SatelliteObservation;
use App\Models\WeatherForecast;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik lahan
        $totalLahan = Land::count();
        $totalLuas = Land::sum('luas');

        // Status kekeringan dari analisis terbaru
        $latestAnalyses = DroughtAnalysis::with('land')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('drought_analyses')
                    ->groupBy('land_id');
            })
            ->get();

        $statusCounts = [
            'HIJAU' => 0,
            'KUNING' => 0,
            'ORANYE' => 0,
            'MERAH' => 0,
        ];

        foreach ($latestAnalyses as $analysis) {
            $statusCounts[$analysis->status] = ($statusCounts[$analysis->status] ?? 0) + 1;
        }

        // Rata-rata NDVI
        $latestNdviAvg = SatelliteObservation::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('satellite_observations')
                ->groupBy('land_id');
        })->avg('ndvi');

        // Curah hujan hari ini (rata-rata semua lahan)
        $todayRainfall = RainfallRecord::where('observation_date', Carbon::today())->avg('rainfall_mm');

        // Curah hujan 7 hari terakhir
        $rainfall7d = RainfallRecord::where('observation_date', '>=', Carbon::today()->subDays(7))->avg('rainfall_mm');

        // Estimasi kebutuhan air total (dari rekomendasi terbaru)
        $totalWaterNeed = Recommendation::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('recommendations')
                ->groupBy('land_id');
        })->sum('water_quota_estimate_mm');

        // Data grafik curah hujan 30 hari (rata-rata semua lahan)
        $rainfallChartData = RainfallRecord::selectRaw('observation_date, AVG(rainfall_mm) as avg_rainfall')
            ->where('observation_date', '>=', Carbon::today()->subDays(30))
            ->groupBy('observation_date')
            ->orderBy('observation_date')
            ->get();

        // Data grafik NDVI rata-rata per tanggal observasi
        $ndviChartData = SatelliteObservation::selectRaw('observation_date, AVG(ndvi) as avg_ndvi')
            ->where('observation_date', '>=', Carbon::today()->subDays(60))
            ->groupBy('observation_date')
            ->orderBy('observation_date')
            ->get();

        // Data cuaca BMKG terkini untuk ringkasan dashboard
        $bmkgLatest = WeatherForecast::where('forecast_datetime', '>=', Carbon::now())
            ->where('source', 'BMKG')
            ->orderBy('forecast_datetime')
            ->first();

        if (! $bmkgLatest) {
            $bmkgLatest = WeatherForecast::orderByDesc('forecast_datetime')->first();
        }

        $bmkg3DayRain = (float) WeatherForecast::where('forecast_datetime', '>=', Carbon::today())
            ->where('forecast_datetime', '<=', Carbon::today()->addDays(3))
            ->where('source', 'BMKG')
            ->avg('rainfall_estimate');

        // Data peta (semua lahan dengan status terbaru)
        $mapLands = Land::with(['droughtAnalyses' => function ($q) {
            $q->latest('analysis_date')->limit(1);
        }])->get();

        // GeoJSON FeatureCollection untuk peta dashboard instan
        $mapGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $mapLands->map(function ($land) {
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
                        'drought_label' => $drought?->status_label ?? 'Air Cukup',
                        'drought_color' => DroughtAnalysis::$statusColors[$drought?->status ?? 'HIJAU'] ?? '#22c55e',
                        'ndvi' => $drought?->ndvi_value,
                        'water_balance' => $drought?->water_balance_value,
                        'url' => route('lands.show', $land->id),
                    ],
                ];
            }),
        ];

        return view('dashboard', compact(
            'totalLahan',
            'totalLuas',
            'statusCounts',
            'latestNdviAvg',
            'todayRainfall',
            'rainfall7d',
            'totalWaterNeed',
            'rainfallChartData',
            'ndviChartData',
            'mapLands',
            'mapGeoJson',
            'bmkgLatest',
            'bmkg3DayRain',
        ));
    }
}
