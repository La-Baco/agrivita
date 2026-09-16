<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            UserSeeder::class,
            CropCoefficientSeeder::class,
            SystemSettingSeeder::class,
            LandSeeder::class,
        ];

        if (config('app.data_mode') === 'DEMO') {
            $seeders[] = DemoDataSeeder::class;
        }

        $this->call($seeders);
    }
}
