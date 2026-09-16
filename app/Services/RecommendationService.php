<?php

namespace App\Services;

use App\Models\Land;
use App\Models\Recommendation;
use Illuminate\Support\Carbon;

class RecommendationService
{
    /**
     * Buat rekomendasi alokasi air berdasarkan hasil analisis kekeringan.
     *
     * Catatan: Rekomendasi ini bersifat estimasi berbasis analisis data spasial.
     * Tidak menggantikan keputusan agronomis dari praktisi/ahli lapangan.
     *
     * @param array{
     *   status: string,
     *   water_balance_value: float,
     *   ndvi_change_pct: float,
     *   forecast_rain_3d: float,
     *   water_need_mm: float,
     *   metrics_json: array<string, mixed>
     * } $droughtAnalysisData
     * @return array<string, mixed>
     */
    public function generateRecommendation(Land $land, array $droughtAnalysisData): array
    {
        $status = $droughtAnalysisData['status'];
        $waterBalance = $droughtAnalysisData['water_balance_value'];
        $ndviChange = $droughtAnalysisData['ndvi_change_pct'];
        $forecastRain = $droughtAnalysisData['forecast_rain_3d'];
        $waterNeed = $droughtAnalysisData['water_need_mm'];
        $totalScore = $droughtAnalysisData['metrics_json']['total_score'] ?? 0;

        // Hitung defisit dan estimasi kuota air
        $waterDeficit = max(0, round($waterNeed - $forecastRain, 2));

        // Level prioritas
        [$priorityLevel, $priorityScore] = $this->determinePriority($status, $totalScore);

        // Bangun narasi alasan (transparan)
        $rationale = $this->buildRationale($land, $status, $waterBalance, $ndviChange, $forecastRain, $waterNeed);

        // Bangun teks rekomendasi
        $recommendationText = $this->buildRecommendationText($land, $priorityLevel, $waterDeficit, $forecastRain, $waterNeed);

        return [
            'land_id' => $land->id,
            'recommendation_date' => Carbon::today()->format('Y-m-d'),
            'priority_level' => $priorityLevel,
            'priority_score' => $priorityScore,
            'rationale' => $rationale,
            'recommendation_text' => $recommendationText,
            'water_deficit_mm' => $waterDeficit,
            'water_quota_estimate_mm' => $waterDeficit,
        ];
    }

    /**
     * Tentukan level prioritas berdasarkan status dan total skor.
     *
     * @return array{0: string, 1: float}
     */
    private function determinePriority(string $status, float $totalScore): array
    {
        return match ($status) {
            'MERAH' => ['TINGGI', $totalScore],
            'ORANYE' => ['SEDANG', $totalScore],
            default => ['RENDAH', $totalScore],
        };
    }

    /**
     * Bangun narasi alasan penetapan prioritas secara transparan.
     */
    private function buildRationale(
        Land $land,
        string $status,
        float $waterBalance,
        float $ndviChange,
        float $forecastRain,
        float $waterNeed,
    ): string {
        $reasons = [];

        if ($waterBalance < 0) {
            $reasons[] = 'defisit neraca air '.abs($waterBalance).' mm';
        }

        if ($ndviChange <= -15) {
            $reasons[] = 'penurunan NDVI sebesar '.abs($ndviChange).'% (indikasi stres vegetasi)';
        }

        if ($forecastRain < $waterNeed * 0.5) {
            $reasons[] = 'prakiraan hujan 3 hari ('.$forecastRain.' mm) jauh di bawah kebutuhan air ('.$waterNeed.' mm)';
        }

        if (empty($reasons)) {
            $reasons[] = 'kondisi air cukup berdasarkan analisis terkini';
        }

        $reasonStr = implode(', ', $reasons);

        return "Lahan {$land->nama_lahan} mendapat prioritas {$this->priorityLabel($status)} karena {$reasonStr}.";
    }

    /**
     * Bangun teks rekomendasi teknis alokasi air.
     */
    private function buildRecommendationText(
        Land $land,
        string $priorityLevel,
        float $waterDeficit,
        float $forecastRain,
        float $waterNeed,
    ): string {
        return match ($priorityLevel) {
            'TINGGI' => "Lahan {$land->nama_lahan} diprioritaskan untuk pemberian air segera. "
                ."Estimasi kebutuhan irigasi: {$waterDeficit} mm. "
                ."Prakiraan hujan ({$forecastRain} mm) diperkirakan belum mencukupi kebutuhan air tanaman ({$waterNeed} mm). "
                .'Disarankan untuk segera mengalokasikan air sesuai kapasitas lahan dan kondisi lapangan.',

            'SEDANG' => "Lahan {$land->nama_lahan} perlu dipantau dan dipertimbangkan untuk pemberian air. "
                ."Estimasi defisit air: {$waterDeficit} mm. "
                ."Jika kondisi tidak membaik dalam 2–3 hari, alokasi irigasi sebesar {$waterDeficit} mm diperkirakan diperlukan.",

            default => "Lahan {$land->nama_lahan} belum membutuhkan pemberian air tambahan berdasarkan analisis terkini. "
                .'Kondisi air diperkirakan masih mencukupi. Pemantauan rutin tetap disarankan.',
        };
    }

    private function priorityLabel(string $status): string
    {
        return match ($status) {
            'MERAH' => 'TINGGI',
            'ORANYE' => 'SEDANG',
            default => 'RENDAH',
        };
    }

    /**
     * Simpan atau perbarui rekomendasi.
     */
    public function saveRecommendation(array $data): Recommendation
    {
        return Recommendation::updateOrCreate(
            ['land_id' => $data['land_id'], 'recommendation_date' => $data['recommendation_date']],
            $data
        );
    }
}
