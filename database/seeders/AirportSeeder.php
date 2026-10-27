<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $airports = [
            ['code' => 'MNL', 'name' => 'Ninoy Aquino International Airport', 'city' => 'Manila', 'country' => 'Philippines'],
            ['code' => 'CEB', 'name' => 'Mactan-Cebu International Airport', 'city' => 'Cebu', 'country' => 'Philippines'],
            ['code' => 'DVO', 'name' => 'Francisco Bangoy International Airport', 'city' => 'Davao', 'country' => 'Philippines'],
            ['code' => 'SIN', 'name' => 'Changi Airport', 'city' => 'Singapore', 'country' => 'Singapore'],
        ];

        foreach ($airports as $airport) {
            Airport::create($airport);
        }
    }
}