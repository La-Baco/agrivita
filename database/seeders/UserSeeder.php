<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@agrivita.id'],
            [
                'name' => 'Dr. Ir. Suryadi, M.Sc (Peneliti Agrivita)',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'peneliti@agrivita.id'],
            [
                'name' => 'Tim Peneliti Agrivita Sumenep',
                'password' => Hash::make('password'),
            ]
        );
    }
}
