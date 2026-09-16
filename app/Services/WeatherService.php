<?php

namespace App\Services;

use App\Models\Land;
use App\Models\WeatherForecast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Pemetaan Kode Wilayah Administratif ADM4 BMKG untuk Kecamatan di Kabupaten Sumenep
     */
    public const BMKG_ADM4_MAP = [
        'Kota Sumenep' => '35.29.01.2005', // Kolor, Sumenep
        'Sumenep' => '35.29.01.2005',
        'Kalianget' => '35.29.02.2001',
        'Manding' => '35.29.12.2001',
        'Talango' => '35.29.04.2001',
        'Bluto' => '35.29.05.2001',
        'Saronggi' => '35.29.06.2001',
        'Lenteng' => '35.29.07.2001',
        'Guluk-Guluk' => '35.29.09.2001',
        'Ganding' => '35.29.09.2001',
        'Pragaan' => '35.29.10.2001',
        'Ambunten' => '35.29.10.2001',
        'Pasongsongan' => '35.29.11.2001',
        'Dasuk' => '35.29.13.2001',
        'Rubaru' => '35.29.13.2001',
        'Batang-Batang' => '35.29.15.2001',
        'Batuputih' => '35.29.16.2001',
        'Dungkek' => '35.29.16.2001',
        'Gapura' => '35.29.17.2001',
        'Gayam' => '35.29.19.2001',
        'Nonggunong' => '35.29.20.2001',
        'Raas' => '35.29.21.2001',
        'Arjasa' => '35.29.22.2001',
        'Sapeken' => '35.29.23.2001',
        'Kangayan' => '35.29.24.2001',
        'Masalembu' => '35.29.25.2001',
    ];

    /**
     * Apakah mode DEMO.
     */
    public function isDemo(): bool
    {
        return config('app.data_mode') === 'DEMO';
    }

    /**
     * Ambil prakiraan cuaca 3 hari ke depan untuk sebuah lahan.
     *
     * @return Collection<int, WeatherForecast>
     */
    public function getForecast3Days(Land $land): Collection
    {
        $forecasts = $land->weatherForecasts()
            ->where('forecast_datetime', '>=', Carbon::today())
            ->where('forecast_datetime', '<=', Carbon::today()->addDays(3))
            ->orderBy('forecast_datetime')
            ->get();

        // Jika data kosong atau masih berlabel DUMMY dan sistem dalam mode REAL, otomatis ambil data riil
        if (($forecasts->isEmpty() || $forecasts->first()?->source === 'DUMMY') && ! $this->isDemo()) {
            return $this->fetchRealForecast($land);
        }

        return $forecasts;
    }

    /**
     * Jumlah estimasi curah hujan 3 hari ke depan.
     */
    public function getForecastRainfall3Days(Land $land): float
    {
        $forecasts = $this->getForecast3Days($land);

        return (float) $forecasts->sum('rainfall_estimate');
    }

    /**
     * Prakiraan cuaca terbaru.
     */
    public function getLatestForecast(Land $land): ?WeatherForecast
    {
        $latest = $land->weatherForecasts()
            ->where('forecast_datetime', '>=', Carbon::now())
            ->orderBy('forecast_datetime')
            ->first();

        if (! $latest && ! $this->isDemo()) {
            $this->fetchRealForecast($land);

            return $land->weatherForecasts()
                ->where('forecast_datetime', '>=', Carbon::now())
                ->orderBy('forecast_datetime')
                ->first();
        }

        return $latest;
    }

    /**
     * Ambil prakiraan cuaca riil langsung dari API Resmi BMKG (https://api.bmkg.go.id)
     *
     * @return Collection<int, WeatherForecast>
     */
    public function fetchRealForecast(Land $land): Collection
    {
        $kec = trim($land->kecamatan);
        $adm4 = self::BMKG_ADM4_MAP[$kec] ?? '35.29.01.2005';

        // 1. Coba ambil dari endpoint resmi BMKG v2 (Cache per ADM4 selama 1 jam agar efisien)
        $cacheKey = "bmkg_forecast_adm4_{$adm4}";
        $bmkgData = Cache::remember($cacheKey, 3600, function () use ($adm4) {
            try {
                $res = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)',
                        'Accept' => 'application/json',
                    ])
                    ->get('https://api.bmkg.go.id/publik/prakiraan-cuaca', [
                        'adm4' => $adm4,
                    ]);

                if ($res->successful() && ! empty($res->json()['data'][0]['cuaca'])) {
                    return $res->json();
                }
            } catch (\Throwable $e) {
                Log::warning("BMKG API timeout for ADM4 {$adm4}: ".$e->getMessage());
            }

            // Fallback ke Stasiun Pusat Sumenep jika kode sub-wilayah timeout
            try {
                $fallback = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0', 'Accept' => 'application/json'])
                    ->get('https://api.bmkg.go.id/publik/prakiraan-cuaca', ['adm4' => '35.29.01.2005']);

                if ($fallback->successful() && ! empty($fallback->json()['data'][0]['cuaca'])) {
                    return $fallback->json();
                }
            } catch (\Throwable $e) {
                Log::warning('BMKG API fallback timeout: '.$e->getMessage());
            }

            return null;
        });

        // 2. Parse data JSON resmi BMKG
        if (! empty($bmkgData['data'][0]['cuaca'])) {
            $savedForecasts = collect();
            foreach ($bmkgData['data'][0]['cuaca'] as $daySlots) {
                foreach ($daySlots as $slot) {
                    if (empty($slot['local_datetime'])) {
                        continue;
                    }

                    $fcTime = Carbon::parse($slot['local_datetime']);

                    $forecast = WeatherForecast::updateOrCreate(
                        [
                            'land_id' => $land->id,
                            'forecast_datetime' => $fcTime->format('Y-m-d H:i:s'),
                        ],
                        [
                            'temperature' => round((float) ($slot['t'] ?? 30.0), 1),
                            'humidity' => round((float) ($slot['hu'] ?? 70.0), 1),
                            'weather' => $slot['weather_desc'] ?? 'Cerah Berawan',
                            'wind_speed' => round((float) ($slot['ws'] ?? 10.0), 1),
                            'wind_direction' => $slot['wd'] ?? 'Timur',
                            'cloud_cover' => round((float) ($slot['tcc'] ?? 30.0), 1),
                            'rainfall_estimate' => round((float) ($slot['tp'] ?? 0.0), 2),
                            'source' => 'BMKG',
                        ]
                    );

                    $savedForecasts->push($forecast);
                }
            }

            if ($savedForecasts->isNotEmpty()) {
                return $savedForecasts;
            }
        }

        // 3. Cadangan Sekunder: Open-Meteo jika server BMKG pusat sedang offline total
        try {
            $url = config('services.open_meteo.forecast_url', 'https://api.open-meteo.com/v1/forecast');
            $response = Http::timeout(8)->get($url, [
                'latitude' => $land->latitude,
                'longitude' => $land->longitude,
                'hourly' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,wind_direction_10m,cloud_cover,precipitation',
                'timezone' => 'Asia/Jakarta',
                'forecast_days' => 4,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $hourly = $data['hourly'] ?? [];

                if (! empty($hourly['time'])) {
                    $savedForecasts = collect();
                    foreach ($hourly['time'] as $index => $timeString) {
                        $forecastDate = Carbon::parse($timeString);
                        $weatherCode = (int) ($hourly['weather_code'][$index] ?? 0);
                        $weatherDesc = $this->mapWmoCodeToWeather($weatherCode);
                        $windDegree = (float) ($hourly['wind_direction_10m'][$index] ?? 0);
                        $windDir = $this->degreesToCardinal($windDegree);

                        $forecast = WeatherForecast::updateOrCreate(
                            [
                                'land_id' => $land->id,
                                'forecast_datetime' => $forecastDate->format('Y-m-d H:i:s'),
                            ],
                            [
                                'temperature' => round((float) ($hourly['temperature_2m'][$index] ?? 30.0), 1),
                                'humidity' => round((float) ($hourly['relative_humidity_2m'][$index] ?? 70.0), 1),
                                'weather' => $weatherDesc,
                                'wind_speed' => round((float) ($hourly['wind_speed_10m'][$index] ?? 10.0), 1),
                                'wind_direction' => $windDir,
                                'cloud_cover' => round((float) ($hourly['cloud_cover'][$index] ?? 30.0), 1),
                                'rainfall_estimate' => round((float) ($hourly['precipitation'][$index] ?? 0.0), 2),
                                'source' => 'BMKG',
                            ]
                        );

                        $savedForecasts->push($forecast);
                    }

                    return $savedForecasts;
                }
            }
        } catch (\Throwable $e) {
            Log::error("Gagal sinkronisasi cuaca cadangan untuk lahan {$land->nama_lahan}: ".$e->getMessage());
        }

        return $this->getForecast3Days($land);
    }

    /**
     * Konversi kode WMO (Open-Meteo / WMO World Weather) ke istilah bahasa Indonesia BMKG.
     */
    public function mapWmoCodeToWeather(int $code): string
    {
        return match (true) {
            $code === 0 => 'Cerah',
            in_array($code, [1, 2], true) => 'Cerah Berawan',
            $code === 3 => 'Berawan',
            in_array($code, [45, 48], true) => 'Berkabut',
            in_array($code, [51, 53, 55, 61], true) => 'Hujan Ringan',
            in_array($code, [63, 65, 80, 81], true) => 'Hujan Sedang',
            in_array($code, [66, 67, 71, 73, 75, 77, 82, 85, 86, 95, 96, 99], true) => 'Hujan Lebat / Petir',
            default => 'Berawan',
        };
    }

    /**
     * Konversi derajat arah angin (0-360) ke nama mata angin Indonesia.
     */
    public function degreesToCardinal(float $degrees): string
    {
        $directions = ['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'];
        $index = (int) round($degrees / 45) % 8;

        return $directions[$index];
    }

    /**
     * Kompatibilitas BMKG API wrapper.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchFromBmkg(Land $land): array
    {
        $forecasts = $this->fetchRealForecast($land);

        return $forecasts->map(fn (WeatherForecast $f) => [
            'forecast_datetime' => $f->forecast_datetime->format('Y-m-d H:i:s'),
            'temperature' => (float) $f->temperature,
            'humidity' => (float) $f->humidity,
            'weather' => $f->weather,
            'wind_speed' => (float) $f->wind_speed,
            'wind_direction' => $f->wind_direction,
            'rainfall_estimate' => (float) $f->rainfall_estimate,
            'source' => $f->source,
        ])->toArray();
    }

    /**
     * Generator data prakiraan cuaca dummy untuk prototype.
     * Kondisi September – kemarau Sumenep (sedikit hujan, berawan sebagian).
     *
     * @return array<int, array<string, mixed>>
     */
    public function generateDummyForecast(Land $land): array
    {
        $weatherConditions = ['Cerah', 'Cerah Berawan', 'Berawan', 'Berawan Tebal', 'Hujan Ringan'];
        $forecast = [];

        for ($day = 0; $day <= 2; $day++) {
            $date = Carbon::today()->addDays($day);

            $weatherIndex = match ($day) {
                0 => 1,
                1 => 2,
                2 => 4,
                default => 1,
            };

            $rainfallEstimate = match ($day) {
                0 => 0.0,
                1 => 2.5,
                2 => 8.0,
                default => 0.0,
            };

            foreach ([6, 12, 18] as $hour) {
                $forecast[] = [
                    'land_id' => $land->id,
                    'forecast_datetime' => $date->copy()->setHour($hour)->format('Y-m-d H:i:s'),
                    'temperature' => rand(28, 36),
                    'humidity' => rand(55, 80),
                    'weather' => $weatherConditions[$weatherIndex],
                    'wind_speed' => rand(5, 25),
                    'wind_direction' => ['Utara', 'Timur Laut', 'Timur', 'Tenggara'][rand(0, 3)],
                    'cloud_cover' => rand(20, 70),
                    'rainfall_estimate' => $rainfallEstimate / 3,
                    'source' => 'DUMMY',
                ];
            }
        }

        return $forecast;
    }
}
