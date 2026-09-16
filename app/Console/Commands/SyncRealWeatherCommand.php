<?php

namespace App\Console\Commands;

use App\Models\Land;
use App\Services\DroughtAnalysisService;
use App\Services\RainfallService;
use App\Services\RecommendationService;
use App\Services\SentinelService;
use App\Services\WaterBalanceService;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncRealWeatherCommand extends Command
{
    protected $signature = 'agrivita:sync-weather {--land= : ID Lahan spesifik yang ingin disinkronkan}';

    protected $description = 'Tarik data curah hujan dan prakiraan cuaca riil via Open-Meteo untuk lahan pertanian';

    public function handle(
        WeatherService $weatherService,
        RainfallService $rainfallService,
        SentinelService $sentinelService,
        WaterBalanceService $waterBalanceService,
        DroughtAnalysisService $droughtService,
        RecommendationService $recommendationService
    ): int {
        $landId = $this->option('land');

        $query = Land::query();
        if ($landId) {
            $query->where('id', $landId);
        }

        $lands = $query->get();

        if ($lands->isEmpty()) {
            $this->warn('Tidak ada data lahan yang ditemukan untuk disinkronkan.');

            return Command::SUCCESS;
        }

        $this->info("Memulai sinkronisasi data cuaca riil untuk {$lands->count()} lahan...");
        $bar = $this->output->createProgressBar($lands->count());
        $bar->start();

        $today = Carbon::today();

        foreach ($lands as $land) {
            // 1. Tarik riwayat curah hujan 30 hari ke belakang
            $rainfallRecords = $rainfallService->fetchRealRainfallHistory($land, 30);

            // 2. Tarik prakiraan cuaca 3-4 hari ke depan
            $forecasts = $weatherService->fetchRealForecast($land);

            // 3. Sinkronkan observasi satelit Sentinel-2
            $sentinelService->syncLandObservations($land);

            // 3. Hitung & simpan neraca air 14 hari terakhir menggunakan data hujan riil
            $runningBalance = 50.0; // Air awal baseline
            for ($i = 13; $i >= 0; $i--) {
                $calcDate = $today->copy()->subDays($i);
                $dayRain = (float) ($rainfallRecords->firstWhere('observation_date', $calcDate->format('Y-m-d'))?->rainfall_mm ?? 0.0);

                $wbData = $waterBalanceService->calculateWaterBalance(
                    $land,
                    $dayRain,
                    irrigationMm: 0.0,
                    initialWaterMm: $runningBalance
                );
                $wbData['calculation_date'] = $calcDate->format('Y-m-d');
                $wb = $waterBalanceService->saveWaterBalance($wbData);
                $runningBalance = (float) $wb->final_water_balance;
            }

            // 4. Jalankan analisis risiko kekeringan berbasis data riil
            $analysisResult = $droughtService->analyzeLand($land);
            $droughtService->saveAnalysis($analysisResult);

            // 5. Terbitkan rekomendasi alokasi air nyata
            $recData = $recommendationService->generateRecommendation($land, $analysisResult);
            $recommendationService->saveRecommendation($recData);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✓ Sukses! Seluruh data curah hujan, prakiraan cuaca, neraca air, dan rekomendasi telah diperbarui dengan data riil.');

        return Command::SUCCESS;
    }
}
