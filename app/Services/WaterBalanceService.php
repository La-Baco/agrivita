<?php

namespace App\Services;

use App\Models\CropCoefficient;
use App\Models\Land;
use App\Models\WaterBalance;
use Illuminate\Support\Carbon;

class WaterBalanceService
{
    /**
     * Konstanta referensi untuk perhitungan ETo (Evapotranspirasi Referensi)
     * menggunakan pendekatan sederhana Blaney-Criddle yang disederhanakan
     * sesuai kondisi iklim lokal Sumenep (musim kemarau September).
     *
     * Sumber: Allen et al. (1998) FAO Irrigation and Drainage Paper No. 56
     * Catatan: Untuk produksi nyata, gunakan data ETo dari stasiun meteorologi setempat.
     */
    private const ET0_DEMO_MM_PER_DAY = 4.5; // mm/hari (estimasi musim kemarau Sumenep)

    private const EFFECTIVE_RAINFALL_FACTOR = 0.75; // 75% hujan efektif (USDA SCS)

    private const PERCOLATION_RATE = 2.0; // mm/hari perkolasi default (tanah lempung berpasir)

    /**
     * Hitung neraca air untuk sebuah lahan berdasarkan data yang tersedia.
     *
     * Rumus Neraca Air:
     * Neraca Akhir = Kandungan Air Awal + Hujan Efektif + Irigasi - ETc - Perkolasi
     *
     * Keterangan:
     * - ETc = ETo × Kc (evapotranspirasi tanaman = ET referensi × koefisien tanaman)
     * - Hujan Efektif = Curah Hujan × Faktor Efektif (USDA SCS: ~75%)
     * - Perkolasi = kehilangan air ke lapisan bawah tanah
     *
     * @return array<string, mixed>
     */
    public function calculateWaterBalance(
        Land $land,
        float $rainfallMm,
        float $irrigationMm = 0.0,
        float $initialWaterMm = 50.0,
        ?float $et0Override = null,
        ?float $kcOverride = null,
    ): array {
        $daysSincePlanting = $land->umur_tanaman;
        $et0 = $et0Override ?? self::ET0_DEMO_MM_PER_DAY;

        // Tentukan nilai Kc dari tabel koefisien tanaman
        $cropCoef = CropCoefficient::findKcForCrop($land->jenis_tanaman, $daysSincePlanting);
        $kc = $kcOverride ?? ($cropCoef?->kc ?? 1.0);
        $growthStage = $cropCoef?->growth_stage ?? 'Tidak Diketahui';

        // Perhitungan ETc (Evapotranspirasi Tanaman)
        $etc = round($et0 * $kc, 2);

        // Hujan efektif (75% dari curah hujan total)
        $effectiveRainfall = round($rainfallMm * self::EFFECTIVE_RAINFALL_FACTOR, 2);

        // Perkolasi
        $waterLoss = self::PERCOLATION_RATE;

        // Neraca Air
        $finalWaterBalance = round(
            $initialWaterMm + $effectiveRainfall + $irrigationMm - $etc - $waterLoss,
            2
        );

        // Kapasitas lapang estimasi (Field Capacity) – default 100 mm
        $fieldCapacity = 100.0;
        $deficitSurplus = round($finalWaterBalance - $fieldCapacity, 2);

        return [
            'land_id' => $land->id,
            'calculation_date' => Carbon::today()->format('Y-m-d'),
            'initial_water' => $initialWaterMm,
            'effective_rainfall' => $effectiveRainfall,
            'irrigation' => $irrigationMm,
            'crop_water_use' => $etc,
            'water_loss' => $waterLoss,
            'final_water_balance' => $finalWaterBalance,
            'deficit_surplus' => $deficitSurplus,
            'details_json' => [
                'et0_mm_per_day' => $et0,
                'kc' => $kc,
                'growth_stage' => $growthStage,
                'days_since_planting' => $daysSincePlanting,
                'rainfall_raw_mm' => $rainfallMm,
                'effective_rainfall_factor' => self::EFFECTIVE_RAINFALL_FACTOR,
                'percolation_rate' => self::PERCOLATION_RATE,
                'field_capacity_mm' => $fieldCapacity,
                'is_demo' => config('app.data_mode') === 'DEMO',
                'formula' => 'Neraca Akhir = Air Awal + Hujan Efektif + Irigasi - ETc - Perkolasi',
                'note' => 'Nilai ETo menggunakan estimasi lokal Sumenep (musim kemarau). Untuk akurasi lebih tinggi, gunakan data stasiun meteorologi.',
            ],
        ];
    }

    /**
     * Simpan hasil perhitungan neraca air ke database.
     */
    public function saveWaterBalance(array $data): WaterBalance
    {
        return WaterBalance::updateOrCreate(
            ['land_id' => $data['land_id'], 'calculation_date' => $data['calculation_date']],
            $data
        );
    }

    /**
     * Estimasi kebutuhan air irigasi (defisit) untuk memenuhi kebutuhan tanaman.
     * Selisih antara ETc dan hujan efektif.
     *
     * @return array{etc_mm: float, effective_rainfall_mm: float, irrigation_need_mm: float, kc: float, growth_stage: string}
     */
    public function estimateIrrigationNeed(Land $land, float $rainfallMm): array
    {
        $daysSincePlanting = $land->umur_tanaman;
        $cropCoef = CropCoefficient::findKcForCrop($land->jenis_tanaman, $daysSincePlanting);
        $kc = $cropCoef?->kc ?? 1.0;
        $etc = round(self::ET0_DEMO_MM_PER_DAY * $kc, 2);
        $effectiveRainfall = round($rainfallMm * self::EFFECTIVE_RAINFALL_FACTOR, 2);
        $irrigationNeed = max(0, round($etc - $effectiveRainfall, 2));

        return [
            'etc_mm' => $etc,
            'effective_rainfall_mm' => $effectiveRainfall,
            'irrigation_need_mm' => $irrigationNeed,
            'kc' => $kc,
            'growth_stage' => $cropCoef?->growth_stage ?? 'Tidak Diketahui',
        ];
    }
}
