<?php

namespace Database\Seeders;

use App\Models\CropCoefficient;
use Illuminate\Database\Seeder;

class CropCoefficientSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Padi Ciherang / Sawah Irigasi
            ['crop_name' => 'Padi Ciherang', 'growth_stage' => 'Awal (Initial/Vegetatif Awal)', 'stage_days_min' => 1, 'stage_days_max' => 20, 'kc' => 1.10, 'description' => 'Fase penggenangan awal & adaptasi bibit (FAO 56)'],
            ['crop_name' => 'Padi Ciherang', 'growth_stage' => 'Vegetatif Aktif (Crop Development)', 'stage_days_min' => 21, 'stage_days_max' => 50, 'kc' => 1.15, 'description' => 'Pertumbuhan anakan maksimum & kanopi menutup'],
            ['crop_name' => 'Padi Ciherang', 'growth_stage' => 'Reproduktif (Mid-Season/Pembungaan)', 'stage_days_min' => 51, 'stage_days_max' => 85, 'kc' => 1.20, 'description' => 'Fase bunting, pembungaan, pengisian bulir (kebutuhan air puncak)'],
            ['crop_name' => 'Padi Ciherang', 'growth_stage' => 'Pematangan (Late Season)', 'stage_days_min' => 86, 'stage_days_max' => 115, 'kc' => 0.90, 'description' => 'Bulir menguning, persiapan panen & pengeringan lahan'],

            // Padi IR64
            ['crop_name' => 'Padi IR64', 'growth_stage' => 'Awal (Initial)', 'stage_days_min' => 1, 'stage_days_max' => 20, 'kc' => 1.10, 'description' => 'Fase anakan awal'],
            ['crop_name' => 'Padi IR64', 'growth_stage' => 'Vegetatif', 'stage_days_min' => 21, 'stage_days_max' => 50, 'kc' => 1.15, 'description' => 'Fase pembentukan anakan produktif'],
            ['crop_name' => 'Padi IR64', 'growth_stage' => 'Reproduktif', 'stage_days_min' => 51, 'stage_days_max' => 80, 'kc' => 1.20, 'description' => 'Fase pembungaan dan pengisian gabah'],
            ['crop_name' => 'Padi IR64', 'growth_stage' => 'Pematangan', 'stage_days_min' => 81, 'stage_days_max' => 110, 'kc' => 0.85, 'description' => 'Pengeringan sebelum panen'],

            // Jagung Hibrida
            ['crop_name' => 'Jagung Hibrida', 'growth_stage' => 'Awal (Perkecambahan)', 'stage_days_min' => 1, 'stage_days_max' => 20, 'kc' => 0.70, 'description' => 'Perkecambahan hingga 4 daun sempurna'],
            ['crop_name' => 'Jagung Hibrida', 'growth_stage' => 'Vegetatif Cepat', 'stage_days_min' => 21, 'stage_days_max' => 50, 'kc' => 0.90, 'description' => 'Pertumbuhan batang & perluasan daun'],
            ['crop_name' => 'Jagung Hibrida', 'growth_stage' => 'Pembungaan & Pengisian Biji', 'stage_days_min' => 51, 'stage_days_max' => 80, 'kc' => 1.20, 'description' => 'Silking & pembentukan tongkol, sangat sensitif kekurangan air'],
            ['crop_name' => 'Jagung Hibrida', 'growth_stage' => 'Pematangan', 'stage_days_min' => 81, 'stage_days_max' => 105, 'kc' => 0.60, 'description' => 'Pengeringan kelobot dan biji'],

            // Jagung Madura (Lokal)
            ['crop_name' => 'Jagung Madura', 'growth_stage' => 'Awal', 'stage_days_min' => 1, 'stage_days_max' => 15, 'kc' => 0.65, 'description' => 'Fase perkecambahan jagung lokal genjah'],
            ['crop_name' => 'Jagung Madura', 'growth_stage' => 'Vegetatif', 'stage_days_min' => 16, 'stage_days_max' => 40, 'kc' => 0.85, 'description' => 'Fase vegetatif tahan kering'],
            ['crop_name' => 'Jagung Madura', 'growth_stage' => 'Pembungaan & Bertongkol', 'stage_days_min' => 41, 'stage_days_max' => 65, 'kc' => 1.15, 'description' => 'Keluar malai dan rambut tongkol'],
            ['crop_name' => 'Jagung Madura', 'growth_stage' => 'Pematangan', 'stage_days_min' => 66, 'stage_days_max' => 85, 'kc' => 0.55, 'description' => 'Pematangan tongkol kering'],

            // Tembakau Prancak (Komoditas Utama Sumenep)
            ['crop_name' => 'Tembakau Prancak', 'growth_stage' => 'Awal (Bibit Pasca Tanam)', 'stage_days_min' => 1, 'stage_days_max' => 20, 'kc' => 0.65, 'description' => 'Fase adaptasi bibit pasca pindah tanam tegalan'],
            ['crop_name' => 'Tembakau Prancak', 'growth_stage' => 'Pertumbuhan Vegetatif', 'stage_days_min' => 21, 'stage_days_max' => 50, 'kc' => 0.85, 'description' => 'Perbanyakan dan pembesaran daun tembakau'],
            ['crop_name' => 'Tembakau Prancak', 'growth_stage' => 'Puncak Pembentukan Daun (Mid)', 'stage_days_min' => 51, 'stage_days_max' => 75, 'kc' => 1.10, 'description' => 'Daun tebal, pemangkasan pucuk (topping)'],
            ['crop_name' => 'Tembakau Prancak', 'growth_stage' => 'Pemetikan Daun (Harvest)', 'stage_days_min' => 76, 'stage_days_max' => 100, 'kc' => 0.75, 'description' => 'Pemetikan daun bertahap dari bawah ke atas'],

            // Bawang Merah Rubaru (Varietas Unggul Lokal Sumenep)
            ['crop_name' => 'Bawang Merah Rubaru', 'growth_stage' => 'Awal (Tunas & Perakaran)', 'stage_days_min' => 1, 'stage_days_max' => 15, 'kc' => 0.70, 'description' => 'Pertumbuhan tunas dan akar umbi'],
            ['crop_name' => 'Bawang Merah Rubaru', 'growth_stage' => 'Vegetatif Aktif', 'stage_days_min' => 16, 'stage_days_max' => 35, 'kc' => 0.90, 'description' => 'Pertumbuhan daun dan anakan'],
            ['crop_name' => 'Bawang Merah Rubaru', 'growth_stage' => 'Pembentukan Umbi (Bulbing)', 'stage_days_min' => 36, 'stage_days_max' => 55, 'kc' => 1.05, 'description' => 'Pembesaran umbi bawang merah Rubaru'],
            ['crop_name' => 'Bawang Merah Rubaru', 'growth_stage' => 'Pematangan Umbi', 'stage_days_min' => 56, 'stage_days_max' => 70, 'kc' => 0.75, 'description' => 'Daun rebah 70%, penghentian penyiraman'],

            // Cabai Jamu (Komoditas Khas Madura)
            ['crop_name' => 'Cabai Jamu', 'growth_stage' => 'Awal Tanam', 'stage_days_min' => 1, 'stage_days_max' => 30, 'kc' => 0.60, 'description' => 'Adaptasi sulur rambat awal'],
            ['crop_name' => 'Cabai Jamu', 'growth_stage' => 'Pertumbuhan Sulur & Daun', 'stage_days_min' => 31, 'stage_days_max' => 90, 'kc' => 0.80, 'description' => 'Perambatan pada tiang panjat'],
            ['crop_name' => 'Cabai Jamu', 'growth_stage' => 'Pembungaan & Buah Muda', 'stage_days_min' => 91, 'stage_days_max' => 150, 'kc' => 1.05, 'description' => 'Pembentukan buah cabai jamu silindris'],
            ['crop_name' => 'Cabai Jamu', 'growth_stage' => 'Pemetikan Berkala', 'stage_days_min' => 151, 'stage_days_max' => 240, 'kc' => 0.85, 'description' => 'Panen buah berkala'],

            // Cabai Merah Besar
            ['crop_name' => 'Cabai Merah Besar', 'growth_stage' => 'Awal', 'stage_days_min' => 1, 'stage_days_max' => 20, 'kc' => 0.60, 'description' => 'Fase vegetatif awal'],
            ['crop_name' => 'Cabai Merah Besar', 'growth_stage' => 'Vegetatif', 'stage_days_min' => 21, 'stage_days_max' => 50, 'kc' => 0.80, 'description' => 'Percabangan dikotomi'],
            ['crop_name' => 'Cabai Merah Besar', 'growth_stage' => 'Berbunga & Berbuah', 'stage_days_min' => 51, 'stage_days_max' => 90, 'kc' => 1.05, 'description' => 'Pembungaan dan pembesaran buah cabai'],
            ['crop_name' => 'Cabai Merah Besar', 'growth_stage' => 'Pematangan & Panen', 'stage_days_min' => 91, 'stage_days_max' => 120, 'kc' => 0.85, 'description' => 'Buah merah siap petik'],

            // Kacang Tanah
            ['crop_name' => 'Kacang Tanah', 'growth_stage' => 'Awal', 'stage_days_min' => 1, 'stage_days_max' => 20, 'kc' => 0.40, 'description' => 'Perkecambahan dan pertumbuhan daun awal'],
            ['crop_name' => 'Kacang Tanah', 'growth_stage' => 'Vegetatif & Pembungaan', 'stage_days_min' => 21, 'stage_days_max' => 50, 'kc' => 0.80, 'description' => 'Bunga muncul dan pembentukan ginofor'],
            ['crop_name' => 'Kacang Tanah', 'growth_stage' => 'Pengisian Polong', 'stage_days_min' => 51, 'stage_days_max' => 80, 'kc' => 1.15, 'description' => 'Ginofor masuk tanah & pembesaran polong'],
            ['crop_name' => 'Kacang Tanah', 'growth_stage' => 'Pematangan', 'stage_days_min' => 81, 'stage_days_max' => 100, 'kc' => 0.60, 'description' => 'Polong tua berurat jelas'],

            // Kedelai
            ['crop_name' => 'Kedelai', 'growth_stage' => 'Awal', 'stage_days_min' => 1, 'stage_days_max' => 15, 'kc' => 0.40, 'description' => 'Fase kemunculan bibit'],
            ['crop_name' => 'Kedelai', 'growth_stage' => 'Vegetatif', 'stage_days_min' => 16, 'stage_days_max' => 40, 'kc' => 0.80, 'description' => 'Percabangan dan nodulasi akar'],
            ['crop_name' => 'Kedelai', 'growth_stage' => 'Pembungaan & Polong', 'stage_days_min' => 41, 'stage_days_max' => 70, 'kc' => 1.15, 'description' => 'Fase pembungaan dan pengisian biji kedelai'],
            ['crop_name' => 'Kedelai', 'growth_stage' => 'Pematangan', 'stage_days_min' => 71, 'stage_days_max' => 90, 'kc' => 0.50, 'description' => 'Daun rontok, polong cokelat'],
        ];

        foreach ($data as $item) {
            CropCoefficient::updateOrCreate(
                [
                    'crop_name' => $item['crop_name'],
                    'growth_stage' => $item['growth_stage'],
                ],
                $item
            );
        }
    }
}
