<?php

namespace Database\Seeders;

use App\Models\Land;
use App\Models\RainfallRecord;
use App\Models\SatelliteObservation;
use App\Models\WaterBalance;
use App\Models\WeatherForecast;
use App\Services\DroughtAnalysisService;
use App\Services\RecommendationService;
use App\Services\WaterBalanceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $lands = Land::all();

        $droughtService = app(DroughtAnalysisService::class);
        $recommendationService = app(RecommendationService::class);
        $waterBalanceService = app(WaterBalanceService::class);

        // Pola kekeringan per kode lahan untuk variasi realistis di Sumenep
        $landProfiles = [
            'LHK-SMP-001' => ['base_rain' => 1.5, 'rain_trend' => 'dry', 'health_trend' => 'declining'], // Gapura (Padi - Waspada)
            'LHK-SMP-002' => ['base_rain' => 2.0, 'rain_trend' => 'moderate', 'health_trend' => 'stable'], // Lenteng (Jagung - Rendah)
            'LHK-SMP-003' => ['base_rain' => 0.2, 'rain_trend' => 'severe_dry', 'health_trend' => 'rapid_drop'], // Guluk-Guluk (Tembakau - Kritis MERAH)
            'LHK-SMP-004' => ['base_rain' => 1.2, 'rain_trend' => 'dry', 'health_trend' => 'declining'], // Rubaru (Bawang Merah - ORANYE)
            'LHK-SMP-005' => ['base_rain' => 3.5, 'rain_trend' => 'moderate', 'health_trend' => 'stable'], // Bluto (Cabai Jamu - KUNING)
            'LHK-SMP-006' => ['base_rain' => 5.5, 'rain_trend' => 'wet', 'health_trend' => 'thriving'], // Saronggi (Padi Irigasi - HIJAU)
            'LHK-SMP-007' => ['base_rain' => 0.4, 'rain_trend' => 'severe_dry', 'health_trend' => 'rapid_drop'], // Ganding (Jagung Madura - MERAH)
            'LHK-SMP-008' => ['base_rain' => 4.0, 'rain_trend' => 'moderate', 'health_trend' => 'thriving'], // Ambunten (Kacang Tanah - HIJAU)
            'LHK-SMP-009' => ['base_rain' => 3.8, 'rain_trend' => 'moderate', 'health_trend' => 'stable'], // Dasuk (Kedelai - HIJAU)
            'LHK-SMP-010' => ['base_rain' => 2.2, 'rain_trend' => 'moderate', 'health_trend' => 'stable'], // Manding (Cabai Merah - KUNING)
        ];

        foreach ($lands as $land) {
            $profile = $landProfiles[$land->kode_lahan] ?? ['base_rain' => 2.0, 'rain_trend' => 'moderate', 'health_trend' => 'stable'];

            // 1. Generate 30 hari data curah hujan historis
            $this->seedRainfallRecords($land, $today, $profile);

            // 2. Generate 3 hari prakiraan cuaca
            $this->seedWeatherForecasts($land, $today, $profile);

            // 3. Generate 12 kali observasi satelit Sentinel-2 (setiap 5 hari selama 60 hari)
            $this->seedSatelliteObservations($land, $today, $profile);

            // 4. Hitung & simpan 14 hari neraca air
            $this->seedWaterBalances($land, $today, $waterBalanceService);

            // 5. Analisis Risiko Kekeringan berbasis aturan
            $analysisResult = $droughtService->analyzeLand($land);
            $droughtService->saveAnalysis($analysisResult);

            // 6. Buat Rekomendasi Alokasi Air Transparan
            $recData = $recommendationService->generateRecommendation($land, $analysisResult);
            $recommendationService->saveRecommendation($recData);
        }
    }

    /**
     * Generate 30 hari data curah hujan historis
     */
    private function seedRainfallRecords(Land $land, Carbon $today, array $profile): void
    {
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            // Variasi acak berbasis profil lahan
            $isDrySpell = ($profile['rain_trend'] === 'severe_dry' && $i < 15) ||
                          ($profile['rain_trend'] === 'dry' && $i < 8);

            if ($isDrySpell) {
                $rainMm = 0.0;
            } else {
                $chanceOfRain = match ($profile['rain_trend']) {
                    'wet' => 0.65,
                    'moderate' => 0.35,
                    'dry' => 0.20,
                    'severe_dry' => 0.10,
                    default => 0.30,
                };

                $hasRain = (mt_rand(1, 100) / 100) <= $chanceOfRain;
                $rainMm = $hasRain ? round($profile['base_rain'] * (mt_rand(5, 25) / 10), 1) : 0.0;
            }

            RainfallRecord::updateOrCreate(
                [
                    'land_id' => $land->id,
                    'observation_date' => $date->format('Y-m-d'),
                ],
                [
                    'rainfall_mm' => $rainMm,
                    'source' => 'DUMMY',
                    'notes' => $rainMm > 0 ? "Curah hujan terukur {$rainMm} mm (DATA DEMO)" : 'Hari tanpa hujan (kering)',
                ]
            );
        }
    }

    /**
     * Generate 3 hari prakiraan cuaca BMKG terdekat
     */
    private function seedWeatherForecasts(Land $land, Carbon $today, array $profile): void
    {
        $forecastProfiles = [
            'severe_dry' => [
                ['rain' => 0.0, 'condition' => 'Cerah', 'temp' => 34.0, 'humidity' => 58, 'wind' => 14, 'cloud' => 10],
                ['rain' => 0.0, 'condition' => 'Cerah Berawan', 'temp' => 34.8, 'humidity' => 55, 'wind' => 15, 'cloud' => 20],
                ['rain' => 0.0, 'condition' => 'Cerah', 'temp' => 35.0, 'humidity' => 56, 'wind' => 12, 'cloud' => 15],
            ],
            'dry' => [
                ['rain' => 0.0, 'condition' => 'Cerah Berawan', 'temp' => 33.0, 'humidity' => 64, 'wind' => 12, 'cloud' => 30],
                ['rain' => 1.5, 'condition' => 'Berawan', 'temp' => 32.5, 'humidity' => 68, 'wind' => 10, 'cloud' => 60],
                ['rain' => 0.0, 'condition' => 'Cerah Berawan', 'temp' => 33.5, 'humidity' => 62, 'wind' => 11, 'cloud' => 35],
            ],
            'wet' => [
                ['rain' => 6.5, 'condition' => 'Hujan Sedang', 'temp' => 29.5, 'humidity' => 84, 'wind' => 16, 'cloud' => 85],
                ['rain' => 4.0, 'condition' => 'Hujan Ringan', 'temp' => 30.0, 'humidity' => 80, 'wind' => 14, 'cloud' => 75],
                ['rain' => 2.5, 'condition' => 'Berawan', 'temp' => 31.0, 'humidity' => 76, 'wind' => 12, 'cloud' => 65],
            ],
            'moderate' => [
                ['rain' => 2.0, 'condition' => 'Hujan Ringan', 'temp' => 31.5, 'humidity' => 72, 'wind' => 11, 'cloud' => 55],
                ['rain' => 0.0, 'condition' => 'Berawan', 'temp' => 32.0, 'humidity' => 70, 'wind' => 10, 'cloud' => 50],
                ['rain' => 1.0, 'condition' => 'Cerah Berawan', 'temp' => 32.5, 'humidity' => 68, 'wind' => 9, 'cloud' => 40],
            ],
        ];

        $forecastDays = $forecastProfiles[$profile['rain_trend']] ?? $forecastProfiles['moderate'];

        foreach ($forecastDays as $dayIndex => $fc) {
            $forecastDatetime = $today->copy()->addDays($dayIndex)->setTime(7, 0, 0);

            WeatherForecast::updateOrCreate(
                [
                    'land_id' => $land->id,
                    'forecast_datetime' => $forecastDatetime->format('Y-m-d H:i:s'),
                ],
                [
                    'temperature' => $fc['temp'],
                    'humidity' => $fc['humidity'],
                    'weather' => $fc['condition'],
                    'wind_speed' => $fc['wind'],
                    'wind_direction' => 'Timur - Tenggara',
                    'cloud_cover' => $fc['cloud'],
                    'rainfall_estimate' => $fc['rain'],
                    'source' => 'DUMMY',
                ]
            );
        }
    }

    /**
     * Generate 12 kali observasi satelit Sentinel-2 (setiap 5 hari selama 60 hari)
     */
    private function seedSatelliteObservations(Land $land, Carbon $today, array $profile): void
    {
        for ($obs = 11; $obs >= 0; $obs--) {
            $date = $today->copy()->subDays($obs * 5);

            // Dinamika NDVI sesuai tren kesehatan tanaman
            if ($profile['health_trend'] === 'rapid_drop') {
                $progress = (11 - $obs) / 11; // 0 awal -> 1 sekarang
                $ndvi = round(0.68 - ($progress * 0.40), 4);
                $b4 = round(0.06 + ($progress * 0.12), 4);
                $b8 = round(0.42 - ($progress * 0.20), 4);
            } elseif ($profile['health_trend'] === 'declining') {
                $progress = (11 - $obs) / 11;
                $ndvi = round(0.65 - ($progress * 0.23), 4);
                $b4 = round(0.07 + ($progress * 0.06), 4);
                $b8 = round(0.40 - ($progress * 0.12), 4);
            } elseif ($profile['health_trend'] === 'thriving') {
                $progress = (11 - $obs) / 11;
                $ndvi = round(0.55 + ($progress * 0.23), 4);
                $b4 = round(0.08 - ($progress * 0.03), 4);
                $b8 = round(0.35 + ($progress * 0.15), 4);
            } else {
                $ndvi = round(0.58 + (sin($obs) * 0.05), 4);
                $b4 = 0.0750;
                $b8 = 0.3800;
            }

            SatelliteObservation::updateOrCreate(
                [
                    'land_id' => $land->id,
                    'observation_date' => $date->format('Y-m-d'),
                ],
                [
                    'b4_red' => $b4,
                    'b8_nir' => $b8,
                    'ndvi' => $ndvi,
                    'cloud_percentage' => mt_rand(2, 18) / 10,
                    'image_url' => null,
                    'source' => 'DUMMY',
                ]
            );
        }
    }

    /**
     * Hitung & simpan 14 hari neraca air beruntun
     */
    private function seedWaterBalances(Land $land, Carbon $today, WaterBalanceService $service): void
    {
        for ($d = 13; $d >= 0; $d--) {
            $date = $today->copy()->subDays($d);

            $rainfallRecord = $land->rainfallRecords()
                ->where('observation_date', $date->format('Y-m-d'))
                ->first();

            $rainMm = $rainfallRecord?->rainfall_mm ?? 0.0;

            // Hitung kebutuhan air dan neraca
            $calc = $service->calculateWaterBalance($land, $rainMm, 0.0, 50.0);
            $calc['calculation_date'] = $date->format('Y-m-d');

            WaterBalance::updateOrCreate(
                [
                    'land_id' => $land->id,
                    'calculation_date' => $date->format('Y-m-d'),
                ],
                $calc
            );
        }
    }
}
