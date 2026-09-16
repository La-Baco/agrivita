<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General & Demo Configuration
            ['key' => 'app_data_mode', 'value' => 'DEMO', 'group' => 'general', 'label' => 'Mode sumber data sistem (DEMO / REAL_API). Jika DEMO, label DATA DEMO wajib ditampilkan pada visualisasi spasial dan grafik.'],
            ['key' => 'kabupaten_target', 'value' => 'Sumenep', 'group' => 'general', 'label' => 'Kabupaten fokus implementasi prototipe inovasi'],
            ['key' => 'provinsi_target', 'value' => 'Jawa Timur', 'group' => 'general', 'label' => 'Provinsi wilayah penelitian'],

            // Thresholds & Weights for Drought Rule-Based Scoring
            ['key' => 'weight_water_balance', 'value' => '0.40', 'group' => 'scoring', 'label' => 'Bobot indikator Neraca Air (Defisit/Surplus Kumulatif) dalam penentuan risiko kekeringan'],
            ['key' => 'weight_ndvi_change', 'value' => '0.30', 'group' => 'scoring', 'label' => 'Bobot indikator Dinamika Kesehatan Vegetasi (Perubahan NDVI Sentinel-2)'],
            ['key' => 'weight_forecast_rain', 'value' => '0.30', 'group' => 'scoring', 'label' => 'Bobot indikator Prakiraan Hujan 3 Hari BMKG'],

            // Classification Thresholds
            ['key' => 'threshold_score_red', 'value' => '70', 'group' => 'thresholds', 'label' => 'Ambang batas skor minimum untuk status Risiko Tinggi / Kritis (MERAH)'],
            ['key' => 'threshold_score_orange', 'value' => '45', 'group' => 'thresholds', 'label' => 'Ambang batas skor minimum untuk status Waspada / Sedang (ORANYE)'],
            ['key' => 'threshold_score_yellow', 'value' => '25', 'group' => 'thresholds', 'label' => 'Ambang batas skor minimum untuk status Perhatian / Rendah (KUNING)'],

            // Rainfall classification thresholds (mm/hari)
            ['key' => 'rain_heavy_threshold', 'value' => '20.0', 'group' => 'rainfall', 'label' => 'Batas curah hujan lebat harian (mm/hari)'],
            ['key' => 'rain_moderate_threshold', 'value' => '5.0', 'group' => 'rainfall', 'label' => 'Batas curah hujan sedang harian (mm/hari)'],
            ['key' => 'rain_dry_threshold', 'value' => '1.0', 'group' => 'rainfall', 'label' => 'Batas hari tanpa hujan / kering (mm/hari)'],

            // API Configuration Placeholders
            ['key' => 'bmkg_api_url', 'value' => 'https://api.bmkg.go.id/publik/prakiraan-cuaca', 'group' => 'api', 'label' => 'Endpoint API Data Terbuka BMKG untuk Kabupaten Sumenep'],
            ['key' => 'copernicus_stac_url', 'value' => 'https://browser.dataspace.copernicus.eu/stac', 'group' => 'api', 'label' => 'Endpoint STAC Sentinel-2 Copernicus Data Space'],
            ['key' => 'nasa_power_api_url', 'value' => 'https://power.larc.nasa.gov/api/temporal/daily/point', 'group' => 'api', 'label' => 'Endpoint NASA POWER Agroclimatology API'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
