<?php

namespace App\Services;

use App\Models\Land;
use App\Models\SatelliteObservation;

class NdviService
{
    /**
     * Hitung NDVI dari nilai reflektansi Band 4 (Red) dan Band 8 (NIR).
     *
     * Rumus: NDVI = (NIR - RED) / (NIR + RED)
     * Referensi: Rouse et al. (1974), Tucker (1979)
     * Band Sentinel-2: B4 = Red, B8 = NIR
     *
     * Rentang nilai NDVI: -1.0 hingga +1.0
     * - NDVI > 0.50 : Vegetasi lebat / kondisi baik
     * - NDVI 0.30 - 0.50 : Vegetasi sedang / perlu dipantau
     * - NDVI < 0.30 : Vegetasi kurang baik / stres air / non-vegetasi
     */
    public function calculate(float $b4Red, float $b8Nir): float
    {
        $denominator = $b8Nir + $b4Red;

        if ($denominator == 0) {
            return 0.0;
        }

        return round(($b8Nir - $b4Red) / $denominator, 4);
    }

    /**
     * Hitung perubahan NDVI antara dua periode dalam persen.
     *
     * Rumus: ((NDVI_sekarang - NDVI_sebelumnya) / |NDVI_sebelumnya|) * 100
     * Nilai negatif = penurunan kondisi vegetasi
     * Nilai positif = peningkatan kondisi vegetasi
     */
    public function calculateChangePercent(float $previousNdvi, float $currentNdvi): float
    {
        if ($previousNdvi == 0) {
            return 0.0;
        }

        return round((($currentNdvi - $previousNdvi) / abs($previousNdvi)) * 100, 2);
    }

    /**
     * Klasifikasi kondisi vegetasi berdasarkan nilai NDVI.
     *
     * Catatan: Nilai NDVI dipengaruhi oleh jenis tanaman, umur tanaman,
     * tutupan lahan, kondisi atmosfer, dan tutupan awan.
     * Gunakan hanya sebagai indikator pendukung, bukan satu-satunya acuan.
     *
     * @return array{status: string, label: string, color: string, description: string}
     */
    public function classifyVegetation(float $ndvi): array
    {
        if ($ndvi >= 0.50) {
            return [
                'status' => 'BAIK',
                'label' => 'Vegetasi Relatif Baik',
                'color' => 'green',
                'description' => 'Kondisi vegetasi relatif baik. Indeks NDVI menunjukkan tutupan vegetasi aktif yang tinggi.',
            ];
        } elseif ($ndvi >= 0.30) {
            return [
                'status' => 'SEDANG',
                'label' => 'Vegetasi Perlu Dipantau',
                'color' => 'yellow',
                'description' => 'Kondisi vegetasi sedang. Pemantauan lanjutan diperlukan untuk mendeteksi potensi stres tanaman.',
            ];
        } elseif ($ndvi >= 0.10) {
            return [
                'status' => 'KURANG_BAIK',
                'label' => 'Vegetasi Kurang Baik',
                'color' => 'orange',
                'description' => 'Kondisi vegetasi kurang baik. Indikasi kemungkinan stres air, penyakit, atau fase panen/bera.',
            ];
        } else {
            return [
                'status' => 'SANGAT_RENDAH',
                'label' => 'Tutupan Vegetasi Sangat Rendah',
                'color' => 'red',
                'description' => 'NDVI sangat rendah. Kemungkinan lahan bera, panen baru, atau permukaan non-vegetasi.',
            ];
        }
    }

    /**
     * Ambil NDVI terbaru dan NDVI sebelumnya, lalu hitung perubahan.
     *
     * @return array{latest: SatelliteObservation|null, previous: SatelliteObservation|null, change_pct: float, classification: array<string, string>}
     */
    public function getLatestNdviWithChange(Land $land): array
    {
        $observations = $land->satelliteObservations()
            ->orderByDesc('observation_date')
            ->limit(2)
            ->get();

        $latest = $observations->first();
        $previous = $observations->skip(1)->first();

        $changePct = 0.0;

        if ($latest && $previous) {
            $changePct = $this->calculateChangePercent($previous->ndvi, $latest->ndvi);
        }

        return [
            'latest' => $latest,
            'previous' => $previous,
            'change_pct' => $changePct,
            'classification' => $latest ? $this->classifyVegetation($latest->ndvi) : [],
        ];
    }
}
