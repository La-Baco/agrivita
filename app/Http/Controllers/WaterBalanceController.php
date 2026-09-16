<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Services\WaterBalanceService;
use Illuminate\View\View;

class WaterBalanceController extends Controller
{
    public function __construct(private readonly WaterBalanceService $waterBalanceService) {}

    public function index(): View
    {
        $lands = Land::with(['waterBalances' => function ($q) {
            $q->latest('calculation_date')->limit(1);
        }])->get();

        return view('water-balance.index', compact('lands'));
    }

    public function show(Land $land): View
    {
        $latestBalance = $land->waterBalances()->latest('calculation_date')->first();
        $history = $land->waterBalances()
            ->orderByDesc('calculation_date')
            ->limit(30)
            ->get();

        $chartData = $history->reverse()->values()->map(fn ($wb) => [
            'date' => $wb->calculation_date->format('Y-m-d'),
            'final_balance' => $wb->final_water_balance,
            'effective_rain' => $wb->effective_rainfall,
            'etc' => $wb->crop_water_use,
        ]);

        return view('water-balance.show', compact('land', 'latestBalance', 'history', 'chartData'));
    }
}
