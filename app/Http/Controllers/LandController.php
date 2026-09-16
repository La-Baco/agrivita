<?php

namespace App\Http\Controllers;

use App\Http\Requests\LandRequest;
use App\Models\Land;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LandController extends Controller
{
    public function index(): View
    {
        $lands = Land::withCount(['rainfallRecords', 'satelliteObservations'])
            ->with(['droughtAnalyses' => function ($q) {
                $q->latest('analysis_date')->limit(1);
            }])
            ->latest()
            ->paginate(15);

        return view('lands.index', compact('lands'));
    }

    public function create(): View
    {
        $jenisTanaman = $this->getJenisTanamanOptions();
        $jenisTanah = $this->getJenisTanahOptions();
        $kecamatanOptions = $this->getKecamatanOptions();

        return view('lands.create', compact('jenisTanaman', 'jenisTanah', 'kecamatanOptions'));
    }

    public function store(LandRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Decode dan simpan polygon GeoJSON
        $data['polygon_geojson'] = json_decode($data['polygon_geojson'], true);

        $land = Land::create($data);

        try {
            Artisan::call('agrivita:sync-weather', ['--land' => $land->id]);
        } catch (\Throwable $e) {
            Log::warning("Gagal auto-sync cuaca lahan {$land->id}: ".$e->getMessage());
        }

        return redirect()->route('lands.index')
            ->with('success', 'Lahan berhasil ditambahkan dan data cuaca riil berhasil disinkronkan.');
    }

    public function show(Land $land): View
    {
        $latestObservation = $land->latestObservation();
        $latestDrought = $land->latestDroughtAnalysis();
        $latestRecommendation = $land->latestRecommendation();
        $latestWaterBalance = $land->waterBalances()->latest('calculation_date')->first();
        $rainfallRecords = $land->rainfallRecords()
            ->orderByDesc('observation_date')
            ->limit(30)
            ->get();

        return view('lands.show', compact(
            'land',
            'latestObservation',
            'latestDrought',
            'latestRecommendation',
            'latestWaterBalance',
            'rainfallRecords',
        ));
    }

    public function edit(Land $land): View
    {
        $jenisTanaman = $this->getJenisTanamanOptions();
        $jenisTanah = $this->getJenisTanahOptions();
        $kecamatanOptions = $this->getKecamatanOptions();

        return view('lands.edit', compact('land', 'jenisTanaman', 'jenisTanah', 'kecamatanOptions'));
    }

    public function update(LandRequest $request, Land $land): RedirectResponse
    {
        $data = $request->validated();
        $data['polygon_geojson'] = json_decode($data['polygon_geojson'], true);

        $land->update($data);

        return redirect()->route('lands.show', $land)
            ->with('success', 'Data lahan berhasil diperbarui.');
    }

    public function destroy(Land $land): RedirectResponse
    {
        $land->delete();

        return redirect()->route('lands.index')
            ->with('success', 'Lahan berhasil dihapus.');
    }

    /**
     * @return list<string>
     */
    private function getJenisTanamanOptions(): array
    {
        return [
            'Padi Ciherang',
            'Padi IR64',
            'Jagung Hibrida',
            'Jagung Manis',
            'Tembakau Prancak',
            'Tembakau Madura',
            'Cabai Jamu',
            'Cabai Merah Besar',
            'Bawang Merah Rubaru',
            'Bawang Merah Nganjuk',
            'Kedelai',
            'Kacang Tanah',
        ];
    }

    /**
     * @return list<string>
     */
    private function getJenisTanahOptions(): array
    {
        return [
            'Grumosol (Vertisol)',
            'Mediteran Merah (Alfisol)',
            'Alluvial (Entisol)',
            'Regosol (Entisol)',
            'Latosol (Oxisol)',
            'Litosol (Entisol)',
            'Andosol (Andisol)',
        ];
    }

    /**
     * @return list<string>
     */
    private function getKecamatanOptions(): array
    {
        return [
            'Lenteng',
            'Ganding',
            'Bluto',
            'Saronggi',
            'Guluk-Guluk',
            'Dasuk',
            'Ambunten',
            'Batuan',
            'Gapura',
            'Manding',
            'Kota Sumenep',
            'Pragaan',
            'Rubaru',
            'Kalianget',
        ];
    }
}
