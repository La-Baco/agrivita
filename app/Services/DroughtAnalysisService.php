<?php

namespace App\Services;

use App\Models\CropCoefficient;
use App\Models\DroughtAnalysis;
use App\Models\Land;
use Illuminate\Support\Carbon;

class DroughtAnalysisService
{
    /**
     * Analisis risiko kekeringan lahan berdasarkan kombinasi indikator.
     *
     * Metode: Rule-based scoring transparan (bukan AI/ML).
     * Setiap indikator diberi bobot dan skor 0-100.
     * Skor akhir gabungan menentukan status kekeringan.
     *
     * Indikator:
     * 1. Neraca Air (Water Balance)    - Bobot 35%
     * 2. Curah Hujan 7 Hari            - Bobot 25%
     * 3. Kondisi NDVI                  - Bobot 20%
     * 4. Prakiraan Hujan 3 Hari        - Bobot 20%
     *
     * Skala Status:
     * HIJAU  (Skor 0–25):  Air Cukup
     * KUNING (Skor 26–50): Defisit Ringan
     * ORANYE (Skor 51–75): Risiko Kekeringan
     * MERAH  (Skor 76–100): Prioritas Pemberian Air
     *
     * @return array<string, mixed>
     */
    public function analyze(
        Land $land,
        float $waterBalanceValue,
        float $rainfall7d,
        float $ndviValue,
        float $ndviChangePct,
        float $forecastRain3d,
        float $waterNeedMm,
    ): array {
        // --- 1. Skor Neraca Air (bobot 35%) ---
        // Skala: neraca positif tinggi = skor rendah (kondisi baik)
        $waterBalanceScore = $this->scoreWaterBalance($waterBalanceValue);

        // --- 2. Skor Curah Hujan 7 Hari (bobot 25%) ---
        $rainfallScore = $this->scoreRainfall7d($rainfall7d);

        // --- 3. Skor NDVI (bobot 20%) ---
        $ndviScore = $this->scoreNdvi($ndviValue, $ndviChangePct);

        // --- 4. Skor Prakiraan Hujan 3 Hari (bobot 20%) ---
        $forecastScore = $this->scoreForecastRain($forecastRain3d, $waterNeedMm);

        // Skor Gabungan (weighted average)
        $totalScore = round(
            ($waterBalanceScore * 0.35) +
            ($rainfallScore * 0.25) +
            ($ndviScore * 0.20) +
            ($forecastScore * 0.20),
            2
        );

        // Tentukan status
        [$status, $statusLabel] = $this->determineStatus($totalScore);

        // Buat catatan analisis yang transparan
        $notes = $this->buildAnalysisNotes(
            $waterBalanceValue, $waterBalanceScore,
            $rainfall7d, $rainfallScore,
            $ndviValue, $ndviChangePct, $ndviScore,
            $forecastRain3d, $waterNeedMm, $forecastScore,
            $totalScore, $status
        );

        return [
            'land_id' => $land->id,
            'analysis_date' => Carbon::today()->format('Y-m-d'),
            'water_balance_value' => $waterBalanceValue,
            'rainfall_7d' => $rainfall7d,
            'ndvi_value' => $ndviValue,
            'ndvi_change_pct' => $ndviChangePct,
            'forecast_rain_3d' => $forecastRain3d,
            'water_need_mm' => $waterNeedMm,
            'status' => $status,
            'status_label' => $statusLabel,
            'analysis_notes' => $notes,
            'metrics_json' => [
                'water_balance_score' => $waterBalanceScore,
                'rainfall_score' => $rainfallScore,
                'ndvi_score' => $ndviScore,
                'forecast_score' => $forecastScore,
                'total_score' => $totalScore,
                'weights' => [
                    'water_balance' => '35%',
                    'rainfall_7d' => '25%',
                    'ndvi' => '20%',
                    'forecast_rain' => '20%',
                ],
                'method' => 'Rule-based weighted scoring',
            ],
        ];
    }

    /**
     * Skor neraca air: semakin negatif, semakin tinggi risiko.
     */
    private function scoreWaterBalance(float $waterBalance): float
    {
        if ($waterBalance >= 20) {
            return 0; // Sangat baik
        } elseif ($waterBalance >= 0) {
            return 25; // Cukup
        } elseif ($waterBalance >= -20) {
            return 55; // Defisit ringan-sedang
        } elseif ($waterBalance >= -40) {
            return 75; // Defisit sedang-berat
        } else {
            return 100; // Defisit sangat berat
        }
    }

    /**
     * Skor curah hujan 7 hari: semakin rendah hujan, semakin tinggi risiko.
     */
    private function scoreRainfall7d(float $rainfall7d): float
    {
        if ($rainfall7d >= 50) {
            return 0; // Hujan lebat cukup
        } elseif ($rainfall7d >= 25) {
            return 20; // Hujan cukup
        } elseif ($rainfall7d >= 10) {
            return 50; // Hujan ringan
        } elseif ($rainfall7d >= 2) {
            return 75; // Hujan sangat sedikit
        } else {
            return 100; // Tidak ada hujan
        }
    }

    /**
     * Skor NDVI: nilai rendah dan tren menurun = risiko lebih tinggi.
     */
    private function scoreNdvi(float $ndvi, float $changePct): float
    {
        // Skor dasar berdasarkan nilai NDVI saat ini
        $baseScore = match (true) {
            $ndvi >= 0.50 => 0,
            $ndvi >= 0.35 => 20,
            $ndvi >= 0.20 => 50,
            $ndvi >= 0.10 => 75,
            default => 100,
        };

        // Penyesuaian berdasarkan tren (perubahan %)
        // Penurunan NDVI >20% menambah skor risiko
        $trendAdjustment = 0;
        if ($changePct <= -30) {
            $trendAdjustment = 20;
        } elseif ($changePct <= -15) {
            $trendAdjustment = 10;
        } elseif ($changePct >= 15) {
            $trendAdjustment = -10; // Peningkatan NDVI mengurangi risiko
        }

        return min(100, max(0, $baseScore + $trendAdjustment));
    }

    /**
     * Skor prakiraan hujan vs kebutuhan air.
     */
    private function scoreForecastRain(float $forecastRain3d, float $waterNeedMm): float
    {
        if ($waterNeedMm <= 0) {
            return 0;
        }

        $coverageRatio = $forecastRain3d / $waterNeedMm;

        if ($coverageRatio >= 1.0) {
            return 0; // Prakiraan hujan mencukupi
        } elseif ($coverageRatio >= 0.6) {
            return 30; // Prakiraan hujan sebagian mencukupi
        } elseif ($coverageRatio >= 0.3) {
            return 65; // Prakiraan hujan tidak cukup
        } else {
            return 100; // Prakiraan hujan sangat minim
        }
    }

    /**
     * Tentukan status kekeringan dari total skor.
     *
     * @return array{0: string, 1: string}
     */
    private function determineStatus(float $totalScore): array
    {
        if ($totalScore <= 25) {
            return ['HIJAU', 'Air Cukup'];
        } elseif ($totalScore <= 50) {
            return ['KUNING', 'Defisit Ringan'];
        } elseif ($totalScore <= 75) {
            return ['ORANYE', 'Risiko Kekeringan'];
        } else {
            return ['MERAH', 'Prioritas Pemberian Air'];
        }
    }

    /**
     * Bangun narasi catatan analisis yang transparan dan mudah dipahami.
     */
    private function buildAnalysisNotes(
        float $waterBalance, float $wbScore,
        float $rainfall7d, float $rfScore,
        float $ndvi, float $ndviChange, float $ndviScore,
        float $forecastRain, float $waterNeed, float $fcScore,
        float $totalScore, string $status
    ): string {
        $notes = [];

        // Neraca air
        if ($waterBalance < 0) {
            $notes[] = "Neraca air defisit {$waterBalance} mm (skor risiko {$wbScore}/100)";
        } else {
            $notes[] = "Neraca air positif {$waterBalance} mm (skor risiko {$wbScore}/100)";
        }

        // Curah hujan
        $notes[] = "Curah hujan 7 hari: {$rainfall7d} mm (skor risiko {$rfScore}/100)";

        // NDVI
        $ndviChangeLabel = $ndviChange > 0 ? "meningkat {$ndviChange}%" : 'menurun '.abs($ndviChange).'%';
        $notes[] = "NDVI: {$ndvi} ({$ndviChangeLabel}) (skor risiko {$ndviScore}/100)";

        // Prakiraan hujan vs kebutuhan
        $gap = round($waterNeed - $forecastRain, 1);
        if ($forecastRain >= $waterNeed) {
            $notes[] = "Prakiraan hujan 3 hari ({$forecastRain} mm) diperkirakan mencukupi kebutuhan air ({$waterNeed} mm) (skor {$fcScore}/100)";
        } else {
            $notes[] = "Prakiraan hujan 3 hari ({$forecastRain} mm) belum mencukupi kebutuhan air ({$waterNeed} mm), estimasi defisit {$gap} mm (skor {$fcScore}/100)";
        }

        // Total
        $notes[] = "Skor risiko gabungan: {$totalScore}/100 → Status: {$status}";

        return implode('. ', $notes).'.';
    }

    /**
     * Jalankan analisis kekeringan otomatis untuk satu lahan berdasarkan data tersimpan.
     *
     * @return array<string, mixed>
     */
    public function analyzeLand(Land $land): array
    {
        // 1. Neraca air terkini
        $latestWb = $land->waterBalances()->latest('calculation_date')->first();
        $waterBalanceValue = (float) ($latestWb?->deficit_surplus ?? -20.0);

        // 2. Curah hujan 7 hari terakhir
        $rainfall7d = (float) $land->rainfallRecords()
            ->where('observation_date', '>=', Carbon::today()->subDays(7))
            ->sum('rainfall_mm');

        // 3. Data NDVI terkini dan perubahan
        $observations = $land->satelliteObservations()
            ->orderByDesc('observation_date')
            ->limit(2)
            ->get();

        $latestObs = $observations->first();
        $prevObs = $observations->count() > 1 ? $observations->last() : null;

        $ndviValue = (float) ($latestObs?->ndvi ?? 0.50);
        $prevNdvi = (float) ($prevObs?->ndvi ?? $ndviValue);
        $ndviChangePct = $prevNdvi > 0 ? round((($ndviValue - $prevNdvi) / $prevNdvi) * 100, 2) : 0.0;

        // 4. Prakiraan hujan 3 hari ke depan
        $forecastRain3d = (float) $land->weatherForecasts()
            ->where('forecast_datetime', '>=', Carbon::today()->startOfDay())
            ->limit(3)
            ->sum('rainfall_estimate');

        // 5. Kebutuhan air tanaman (ETc estimasi 3 hari)
        $cropCoef = CropCoefficient::findKcForCrop($land->jenis_tanaman, $land->umur_tanaman);
        $kc = $cropCoef?->kc ?? 1.0;
        $waterNeedMm = round(4.5 * $kc * 3, 2); // 3 hari kebutuhan ETc

        return $this->analyze(
            $land,
            $waterBalanceValue,
            $rainfall7d,
            $ndviValue,
            $ndviChangePct,
            $forecastRain3d,
            $waterNeedMm
        );
    }

    /**
     * Simpan atau perbarui hasil analisis kekeringan.
     */
    public function saveAnalysis(array $data): DroughtAnalysis
    {
        return DroughtAnalysis::updateOrCreate(
            ['land_id' => $data['land_id'], 'analysis_date' => $data['analysis_date']],
            $data
        );
    }
}
