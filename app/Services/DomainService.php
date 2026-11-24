<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\PassengerSeat;
use Illuminate\Support\Facades\DB;

class DomainService
{
    public function createFlight(array $data): Flight
    {
        return DB::transaction(function () use ($data) {
            if (Airport::count() === 0) {
                Airport::create(['code' => 'MNL', 'name' => 'Ninoy Aquino', 'city' => 'Manila', 'country' => 'Philippines']);
                Airport::create(['code' => 'CEB', 'name' => 'Mactan Cebu', 'city' => 'Cebu', 'country' => 'Philippines']);
            } // ANO NI?

            $firstAirport = Airport::first();
            $secondAirport = Airport::skip(1)->first() ?? $firstAirport; // WHY U SKIP THE FIRST 1?

            $data['origin_airport_id'] = $firstAirport->id;
            $data['destination_airport_id'] = $secondAirport->id;

            $clean = $this->sanitizeFlightData($data);
            $flight = Flight::create($clean);
            $this->ensureSeatsForFlight($flight);

            return $flight->load(['originAirport', 'destinationAirport']);
        });
    }

    public function updateFlight(Flight $flight, array $data): Flight
    {
        return DB::transaction(function () use ($flight, $data) {
            $clean = $this->sanitizeFlightData($data);
            $flight->update($clean);
            return $flight->fresh(['originAirport', 'destinationAirport']);
        });
    }

    private function sanitizeFlightData(array $data): array
    {
        return [
            'flight_number'          => strtoupper(trim($data['flight_number'])),
            'origin_airport_id'      => $data['origin_airport_id'],
            'destination_airport_id' => $data['destination_airport_id'],
            'departure_time'         => $data['departure_time'],
            'arrival_time'           => $data['arrival_time'],
            'base_fare'              => round((float) $data['base_fare'], 2),
        ];
    }

    public function ensureSeatsForFlight(Flight $flight): void
    {
        if ($flight->passengerSeats()->exists()) {
            return;
        }

        $layout = [
            ['class' => 'first',    'rows' => 2,  'letters' => ['A', 'B']],
            ['class' => 'business', 'rows' => 4,  'letters' => ['A', 'B', 'C', 'D']],
            ['class' => 'economy',  'rows' => 20, 'letters' => ['A', 'B', 'C', 'D', 'E', 'F']],
        ];

        $seats = [];
        $now   = now();

        foreach ($layout as $block) {
            for ($row = 1; $row <= $block['rows']; $row++) {
                foreach ($block['letters'] as $letter) {
                    $seats[] = [
                        'flight_id'    => $flight->id,
                        'seat_number'  => $row . $letter,
                        'seat_class'   => $block['class'],
                        'is_available' => true,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }
            }
        }
        PassengerSeat::insert($seats);
    }
}
