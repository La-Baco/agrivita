<?php

namespace App\Http\Controllers;

use App\Models\CropCoefficient;
use App\Models\Land;
use App\Services\WaterBalanceService;
use App\Services\WeatherService;
use Illuminate\View\View;

class WaterNeedController extends Controller
{
    public function __construct(
        private readonly WaterBalanceService $waterBalanceService,
        private readonly WeatherService $weatherService,
    ) {}

    public function index(): View
    {
        $lands = Land::all()->map(function (Land $land) {
            $rainfallSum7d = $land->rainfallRecords()
                ->where('observation_date', '>=', now()->subDays(7))
                ->sum('rainfall_mm');

            $forecastRain3d = $this->weatherService->getForecastRainfall3Days($land);

            $irrigationNeed = $this->waterBalanceService->estimateIrrigationNeed($land, $rainfallSum7d / 7);

            $waterNeedMm = $irrigationNeed['etc_mm'];
            $coverageByForecast = min(100, $forecastRain3d > 0 ? round(($forecastRain3d / $waterNeedMm) * 100, 1) : 0);
            $deficitMm = max(0, round($waterNeedMm - $forecastRain3d, 2));

            return [
                'land' => $land,
                'etc_mm' => $irrigationNeed['etc_mm'],
                'kc' => $irrigationNeed['kc'],
                'growth_stage' => $irrigationNeed['growth_stage'],
                'effective_rainfall_mm' => $irrigationNeed['effective_rainfall_mm'],
                'irrigation_need_mm' => $irrigationNeed['irrigation_need_mm'],
                'forecast_rain_3d' => $forecastRain3d,
                'coverage_by_forecast_pct' => $coverageByForecast,
                'deficit_mm' => $deficitMm,
                'forecast_sufficient' => $forecastRain3d >= $waterNeedMm,
            ];
        });

        $cropCoefficients = CropCoefficient::orderBy('crop_name')->orderBy('stage_days_min')->get();

        return view('water-needs.index', compact('lands', 'cropCoefficients'));
    }
}
