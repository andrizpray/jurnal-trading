<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@cuanhunters.com'],
            [
                'name'            => 'Demo Trader',
                'password'        => Hash::make('password'),
                'currency'        => 'IDR',
                'default_capital' => 5000000,
                'trader_type'     => 'moderate',
                'timezone'        => 'Asia/Jakarta',
            ]
        );
    }
}
