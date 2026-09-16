<?php

namespace App\Http\Controllers;

use App\Models\Land;
use App\Services\NdviService;
use App\Services\SentinelService;
use Illuminate\View\View;

class NdviController extends Controller
{
    public function __construct(
        private readonly NdviService $ndviService,
        private readonly SentinelService $sentinelService,
    ) {}

    public function index(): View
    {
        $isDemo = $this->sentinelService->isDemo();

        $lands = Land::all()->map(function (Land $land) {
            $ndviData = $this->ndviService->getLatestNdviWithChange($land);

            return [
                'land' => $land,
                'latest_ndvi' => (float) ($ndviData['latest']?->ndvi ?? 0.0),
                'previous_ndvi' => (float) ($ndviData['previous']?->ndvi ?? 0.0),
                'change_pct' => (float) $ndviData['change_pct'],
                'classification' => $ndviData['classification']['label'] ?? 'Vegetasi Terpantau',
            ];
        });

        return view('ndvi.index', compact('lands', 'isDemo'));
    }

    public function show(Land $land): View
    {
        $isDemo = $this->sentinelService->isDemo();
        $rawNdvi = $this->ndviService->getLatestNdviWithChange($land);
        $ndviData = [
            'latest' => (float) ($rawNdvi['latest']?->ndvi ?? 0.0),
            'previous' => (float) ($rawNdvi['previous']?->ndvi ?? 0.0),
            'change_pct' => (float) $rawNdvi['change_pct'],
            'classification' => $rawNdvi['classification']['label'] ?? 'Vegetasi Terpantau',
        ];
        $history = $this->sentinelService->getObservationHistory($land, 12);

        // Data grafik perubahan NDVI
        $chartData = $history->map(fn ($obs) => [
            'date' => $obs->observation_date->format('Y-m-d'),
            'ndvi' => $obs->ndvi,
        ]);

        return view('ndvi.show', compact('land', 'isDemo', 'ndviData', 'history', 'chartData'));
    }
}
