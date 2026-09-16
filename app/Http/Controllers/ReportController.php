<?php

namespace App\Http\Controllers;

use App\Models\DroughtAnalysis;
use App\Models\Land;
use App\Models\Recommendation;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $totalLahan = Land::count();
        $totalLuas = Land::sum('luas');

        $droughtSummary = DroughtAnalysis::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')->from('drought_analyses')->groupBy('land_id');
        })->select('status', \DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status');

        $prioritySummary = Recommendation::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')->from('recommendations')->groupBy('land_id');
        })->select('priority_level', \DB::raw('COUNT(*) as count'))->groupBy('priority_level')->pluck('count', 'priority_level');

        $lands = Land::with([
            'droughtAnalyses' => fn ($q) => $q->latest('analysis_date')->limit(1),
            'recommendations' => fn ($q) => $q->latest('recommendation_date')->limit(1),
            'satelliteObservations' => fn ($q) => $q->latest('observation_date')->limit(1),
            'waterBalances' => fn ($q) => $q->latest('calculation_date')->limit(1),
        ])->orderBy('nama_lahan')->get();

        $reportDate = Carbon::today()->format('d F Y');

        return view('reports.index', compact(
            'totalLahan',
            'totalLuas',
            'droughtSummary',
            'prioritySummary',
            'lands',
            'reportDate',
        ));
    }
}
