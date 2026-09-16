<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\View\View;

class PriorityController extends Controller
{
    public function index(): View
    {
        // Ambil rekomendasi terbaru per lahan, urutkan prioritas
        $priorityOrder = ['TINGGI' => 0, 'SEDANG' => 1, 'RENDAH' => 2];

        $recommendations = Recommendation::with('land')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('recommendations')
                    ->groupBy('land_id');
            })
            ->get()
            ->sortBy([
                fn ($a, $b) => ($priorityOrder[$a->priority_level] ?? 3) <=> ($priorityOrder[$b->priority_level] ?? 3),
                fn ($a, $b) => $b->priority_score <=> $a->priority_score,
            ])
            ->values();

        $stats = [
            'tinggi' => $recommendations->where('priority_level', 'TINGGI')->count(),
            'sedang' => $recommendations->where('priority_level', 'SEDANG')->count(),
            'rendah' => $recommendations->where('priority_level', 'RENDAH')->count(),
            'total_deficit_mm' => $recommendations->sum('water_deficit_mm'),
            'total_quota_mm' => $recommendations->sum('water_quota_estimate_mm'),
        ];

        return view('priority.index', compact('recommendations', 'stats'));
    }
}
