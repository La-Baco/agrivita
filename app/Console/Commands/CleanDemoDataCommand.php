<?php

namespace App\Console\Commands;

use App\Models\DroughtAnalysis;
use App\Models\Land;
use App\Models\RainfallRecord;
use App\Models\Recommendation;
use App\Models\SatelliteObservation;
use App\Models\SystemSetting;
use App\Models\WaterBalance;
use App\Models\WeatherForecast;
use Illuminate\Console\Command;

class CleanDemoDataCommand extends Command
{
    protected $signature = 'agrivita:clean-demo {--with-lands : Hapus juga seluruh data master lahan demo}';

    protected $description = 'Bersihkan data demo/dummy dan beralih ke mode data riil (Live/Production)';

    public function handle(): int
    {
        $this->info('Membersihkan data demo/dummy AgriVita...');

        // 1. Bersihkan transaksi rekomendasi & analisis risiko dummy
        $deletedRecs = Recommendation::query()->delete();
        $this->line("• Dihapus {$deletedRecs} rekomendasi alokasi air demo.");

        $deletedAnalyses = DroughtAnalysis::query()->delete();
        $this->line("• Dihapus {$deletedAnalyses} riwayat analisis risiko kekeringan demo.");

        $deletedBalances = WaterBalance::query()->delete();
        $this->line("• Dihapus {$deletedBalances} data neraca air demo.");

        // 2. Bersihkan observasi satelit dummy
        $deletedSatellites = SatelliteObservation::where('source', 'DUMMY')->delete();
        $this->line("• Dihapus {$deletedSatellites} observasi satelit dummy.");

        // 3. Bersihkan prakiraan cuaca dummy
        $deletedForecasts = WeatherForecast::where('source', 'DUMMY')->delete();
        $this->line("• Dihapus {$deletedForecasts} data prakiraan cuaca dummy.");

        // 4. Bersihkan catatan curah hujan dummy
        $deletedRainfalls = RainfallRecord::where('source', 'DUMMY')->delete();
        $this->line("• Dihapus {$deletedRainfalls} catatan curah hujan dummy.");

        // 5. Opsional: Hapus lahan demo jika diminta
        if ($this->option('with-lands')) {
            $deletedLands = Land::where('kode_lahan', 'like', 'LHK-SMP-%')->delete();
            $this->warn("• Dihapus {$deletedLands} lahan demo percontohan.");
        } else {
            $this->info('• Data profil master lahan tetap dipertahankan untuk dapat disinkronkan dengan data cuaca riil.');
        }

        // 6. Update status setting ke REAL
        SystemSetting::setValue('app_data_mode', 'REAL');

        $this->newLine();
        $this->info('✓ Sukses! Sistem AgriVita sekarang bersih dari data demo dan beralih ke MODE DATA RIIL.');
        $this->comment('Langkah selanjutnya: Jalankan "php artisan agrivita:sync-weather" untuk menarik data cuaca riil.');

        return Command::SUCCESS;
    }
}
