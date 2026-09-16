<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Services\RainfallService;
use Illuminate\View\View;

class RainfallController extends Controller
{
    public function __construct(private readonly RainfallService $rainfallService) {}

    public function index(): View
    {
        $lands = Land::with(['rainfallRecords' => function ($q) {
            $q->orderByDesc('observation_date')->limit(30);
        }])->get();

        $isDemo = $this->rainfallService->isDemo();

        // Ringkasan per lahan
        $landSummaries = $lands->map(function (Land $land) {
            return [
                'land' => $land,
                'today' => $this->rainfallService->getTodayRainfall($land),
                'sum_7d' => $this->rainfallService->getRainfallSumForDays($land, 7),
                'sum_30d' => $this->rainfallService->getRainfallSumForDays($land, 30),
                'trend' => $this->rainfallService->getRainfallTrend($land),
                'chart_data' => $this->rainfallService->getDailyRainfallForChart($land, 30),
            ];
        });

        return view('rainfall.index', compact('landSummaries', 'isDemo'));
    }

    public function show(Land $land): View
    {
        $isDemo = $this->rainfallService->isDemo();
        $chartData = $this->rainfallService->getDailyRainfallForChart($land, 30);
        $today = $this->rainfallService->getTodayRainfall($land);
        $sum7d = $this->rainfallService->getRainfallSumForDays($land, 7);
        $sum30d = $this->rainfallService->getRainfallSumForDays($land, 30);
        $trend = $this->rainfallService->getRainfallTrend($land);
        $records = $land->rainfallRecords()->orderByDesc('observation_date')->limit(30)->get();

        return view('rainfall.show', compact('land', 'isDemo', 'chartData', 'today', 'sum7d', 'sum30d', 'trend', 'records'));
    }
}
