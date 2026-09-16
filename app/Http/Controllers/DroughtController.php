<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Services\DroughtAnalysisService;
use Illuminate\View\View;

class DroughtController extends Controller
{
    public function __construct(private readonly DroughtAnalysisService $droughtService) {}

    public function index(): View
    {
        $lands = Land::with(['droughtAnalyses' => function ($q) {
            $q->latest('analysis_date')->limit(1);
        }])->get();

        $statusSummary = [
            'HIJAU' => 0, 'KUNING' => 0, 'ORANYE' => 0, 'MERAH' => 0,
        ];

        foreach ($lands as $land) {
            $latest = $land->droughtAnalyses->first();
            if ($latest) {
                $statusSummary[$latest->status] = ($statusSummary[$latest->status] ?? 0) + 1;
            }
        }

        return view('drought.index', compact('lands', 'statusSummary'));
    }

    public function show(Land $land): View
    {
        $latestAnalysis = $land->latestDroughtAnalysis();
        $history = $land->droughtAnalyses()
            ->orderByDesc('analysis_date')
            ->limit(30)
            ->get();

        $chartData = $history->reverse()->values()->map(fn ($a) => [
            'date' => $a->analysis_date->format('Y-m-d'),
            'water_balance' => $a->water_balance_value,
            'ndvi' => $a->ndvi_value,
            'status' => $a->status,
        ]);

        return view('drought.show', compact('land', 'latestAnalysis', 'history', 'chartData'));
    }
}
