<?php

use App\Models\Flight;
use App\Services\DomainService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('flights:generate-missing-seats', function (DomainService $service) {
    $generated = 0;

    Flight::query()
        ->whereDoesntHave('passengerSeats')
        ->chunkById(100, function ($flights) use ($service, &$generated) {
            foreach ($flights as $flight) {
                $service->ensureSeatsForFlight($flight);
                $generated++;
            }
        });

    $this->info("Generated seats for {$generated} flight(s).");
})->purpose('Generate seats for existing flights that do not have any');
