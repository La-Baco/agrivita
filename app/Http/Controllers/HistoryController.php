<?php

namespace App\Http\Controllers;

use App\Models\Land;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $lands = Land::orderBy('nama_lahan')->get();

        return view('history.index', compact('lands'));
    }

    public function show(Land $land): View
    {
        $rainfallHistory = $land->rainfallRecords()
            ->orderByDesc('observation_date')
            ->limit(30)
            ->get();

        $ndviHistory = $land->satelliteObservations()
            ->orderByDesc('observation_date')
            ->limit(12)
            ->get();

        $waterBalanceHistory = $land->waterBalances()
            ->orderByDesc('calculation_date')
            ->limit(30)
            ->get();

        $droughtHistory = $land->droughtAnalyses()
            ->orderByDesc('analysis_date')
            ->limit(30)
            ->get();

        // Data grafik gabungan (timeline)
        $timelineData = $droughtHistory->reverse()->values()->map(fn ($d) => [
            'date' => $d->analysis_date->format('Y-m-d'),
            'status' => $d->status,
            'water_balance' => $d->water_balance_value,
            'ndvi' => $d->ndvi_value,
            'rainfall_7d' => $d->rainfall_7d,
        ]);

        return view('history.show', compact(
            'land',
            'rainfallHistory',
            'ndviHistory',
            'waterBalanceHistory',
            'droughtHistory',
            'timelineData',
        ));
    }
}
