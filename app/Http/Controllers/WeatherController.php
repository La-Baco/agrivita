<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Services\WeatherService;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function __construct(private readonly WeatherService $weatherService) {}

    public function index(): View
    {
        $lands = Land::all();
        $isDemo = $this->weatherService->isDemo();

        $forecasts = $lands->map(function (Land $land) {
            return [
                'land' => $land,
                'forecast' => $this->weatherService->getForecast3Days($land),
                'latest' => $this->weatherService->getLatestForecast($land),
                'rain_3d' => $this->weatherService->getForecastRainfall3Days($land),
            ];
        });

        return view('weather.index', compact('forecasts', 'isDemo'));
    }

    public function show(Land $land): View
    {
        $isDemo = $this->weatherService->isDemo();
        $forecast3Days = $this->weatherService->getForecast3Days($land);
        $totalRain3d = $this->weatherService->getForecastRainfall3Days($land);
        $latestForecast = $this->weatherService->getLatestForecast($land);

        return view('weather.show', compact('land', 'isDemo', 'forecast3Days', 'totalRain3d', 'latestForecast'));
    }
}
