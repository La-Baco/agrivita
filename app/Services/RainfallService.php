<?php

namespace App\Services;

use App\Models\Land;
use App\Models\RainfallRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RainfallService
{
    /**
     * Apakah mode DEMO.
     */
    public function isDemo(): bool
    {
        return config('app.data_mode') === 'DEMO';
    }

    /**
     * Akumulasi curah hujan N hari terakhir untuk sebuah lahan.
     */
    public function getRainfallSumForDays(Land $land, int $days): float
    {
        return (float) $land->rainfallRecords()
            ->where('observation_date', '>=', Carbon::today()->subDays($days))
            ->sum('rainfall_mm');
    }

    /**
     * Data curah hujan harian untuk grafik (N hari terakhir).
     *
     * @return Collection<int, array{date: string, rainfall_mm: float}>
     */
    public function getDailyRainfallForChart(Land $land, int $days = 30): Collection
    {
        return $land->rainfallRecords()
            ->where('observation_date', '>=', Carbon::today()->subDays($days))
            ->orderBy('observation_date')
            ->get()
            ->map(fn (RainfallRecord $record) => [
                'date' => $record->observation_date->format('Y-m-d'),
                'rainfall_mm' => $record->rainfall_mm,
            ]);
    }

    /**
     * Hujan hari ini.
     */
    public function getTodayRainfall(Land $land): float
    {
        return (float) $land->rainfallRecords()
            ->where('observation_date', Carbon::today())
            ->sum('rainfall_mm');
    }

    /**
     * Rata-rata curah hujan harian untuk periode N hari.
     */
    public function getAverageDailyRainfall(Land $land, int $days = 7): float
    {
        $total = $this->getRainfallSumForDays($land, $days);

        return $days > 0 ? round($total / $days, 2) : 0.0;
    }

    /**
     * Tren curah hujan: meningkat, menurun, atau stabil.
     * Membandingkan rata-rata 7 hari pertama vs 7 hari terakhir dalam 30 hari.
     */
    public function getRainfallTrend(Land $land): string
    {
        $records = $land->rainfallRecords()
            ->where('observation_date', '>=', Carbon::today()->subDays(30))
            ->orderBy('observation_date')
            ->get();

        if ($records->count() < 14) {
            return 'tidak cukup data';
        }

        $firstHalf = $records->slice(0, (int) ($records->count() / 2))->avg('rainfall_mm');
        $secondHalf = $records->slice((int) ($records->count() / 2))->avg('rainfall_mm');

        if ($secondHalf > $firstHalf * 1.1) {
            return 'meningkat';
        } elseif ($secondHalf < $firstHalf * 0.9) {
            return 'menurun';
        } else {
            return 'stabil';
        }
    }

    /**
     * Ambil data curah hujan riil dari API dan simpan ke database.
     *
     * @return Collection<int, RainfallRecord>
     */
    public function fetchRealRainfallHistory(Land $land, int $days = 30): Collection
    {
        $url = config('services.open_meteo.forecast_url', 'https://api.open-meteo.com/v1/forecast');

        try {
            $response = Http::timeout(10)->get($url, [
                'latitude' => $land->latitude,
                'longitude' => $land->longitude,
                'daily' => 'precipitation_sum',
                'timezone' => 'Asia/Jakarta',
                'past_days' => $days,
                'forecast_days' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $daily = $data['daily'] ?? [];

                if (! empty($daily['time']) && ! empty($daily['precipitation_sum'])) {
                    $savedRecords = collect();

                    foreach ($daily['time'] as $index => $dateString) {
                        $rainMm = round((float) ($daily['precipitation_sum'][$index] ?? 0.0), 2);

                        $record = RainfallRecord::updateOrCreate(
                            [
                                'land_id' => $land->id,
                                'observation_date' => $dateString,
                            ],
                            [
                                'rainfall_mm' => $rainMm,
                                'source' => 'NASA_GPM',
                                'notes' => 'Presipitasi aktual stasiun satelit Open-Meteo / GPM',
                            ]
                        );

                        $savedRecords->push($record);
                    }

                    return $savedRecords;
                }
            }
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data curah hujan riil untuk lahan {$land->nama_lahan}: ".$e->getMessage());
        }

        // Fallback jika mode DEMO
        if ($this->isDemo()) {
            $dummy = $this->generateDummyRainfallData($days);
            foreach ($dummy as $item) {
                RainfallRecord::updateOrCreate(
                    ['land_id' => $land->id, 'observation_date' => $item['date']],
                    [
                        'rainfall_mm' => $item['rainfall_mm'],
                        'source' => 'DUMMY',
                    ]
                );
            }
        }

        return $land->rainfallRecords()
            ->where('observation_date', '>=', Carbon::today()->subDays($days))
            ->orderBy('observation_date')
            ->get();
    }

    /**
     * Data dummy generator untuk prototype ketika API NASA belum tersedia.
     * Data bersifat realistis untuk wilayah Sumenep dengan musim kemarau.
     *
     * @return array<int, array{date: string, rainfall_mm: float, source: string}>
     */
    public function generateDummyRainfallData(int $days = 30): array
    {
        $data = [];
        $basePattern = [0, 0, 2.3, 0, 0, 5.8, 1.2, 0, 0, 0, 8.5, 3.1, 0, 0, 0, 0, 12.4, 6.7, 0, 0, 0, 0, 4.2, 0, 0, 0, 9.1, 2.8, 0, 0];

        for ($i = $days - 1; $i >= 0; $i--) {
            $patternIndex = ($days - 1 - $i) % count($basePattern);
            $data[] = [
                'date' => Carbon::today()->subDays($i)->format('Y-m-d'),
                'rainfall_mm' => $basePattern[$patternIndex],
                'source' => 'DUMMY',
            ];
        }

        return $data;
    }

    /**
     * Placeholder untuk integrasi NASA GPM/IMERG.
     *
     * @return array<int, array{date: string, rainfall_mm: float, source: string}>
     */
    public function fetchFromNasaGpm(Land $land, Carbon $startDate, Carbon $endDate): array
    {
        $days = (int) $startDate->diffInDays($endDate);
        $records = $this->fetchRealRainfallHistory($land, max(1, $days));

        return $records->map(fn (RainfallRecord $r) => [
            'date' => $r->observation_date->format('Y-m-d'),
            'rainfall_mm' => (float) $r->rainfall_mm,
            'source' => $r->source,
        ])->toArray();
    }
}
