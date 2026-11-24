<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FlightSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_flights_can_be_searched_by_number_airport_or_city(): void
    {
        $manila = Airport::create([
            'code' => 'MNL',
            'name' => 'Manila Airport',
            'city' => 'Manila',
            'country' => 'Philippines',
        ]);
        $cebu = Airport::create([
            'code' => 'CEB',
            'name' => 'Cebu Airport',
            'city' => 'Cebu',
            'country' => 'Philippines',
        ]);
        $singapore = Airport::create([
            'code' => 'SIN',
            'name' => 'Changi Airport',
            'city' => 'Singapore',
            'country' => 'Singapore',
        ]);

        Flight::create([
            'flight_number' => 'GA101',
            'origin_airport_id' => $manila->code,
            'destination_airport_id' => $cebu->code,
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
        Flight::create([
            'flight_number' => 'GA202',
            'origin_airport_id' => $singapore->code,
            'destination_airport_id' => $manila->code,
            'departure_time' => now()->addDays(2),
            'base_fare' => 4000,
        ]);

        $this->actingAs(User::factory()->create());

        foreach ([
            'GA101' => 'GA101',
            'SIN' => 'GA202',
            'Changi Airport' => 'GA202',
            'Singapore' => 'GA202',
            'CEB' => 'GA101',
            'Cebu Airport' => 'GA101',
            'Cebu' => 'GA101',
        ] as $search => $flightNumber) {
            $this->get('/flights?search='.urlencode($search))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('flights/index')
                    ->where('search', $search)
                    ->has('flights.data', 1)
                    ->where('flights.data.0.flight_number', $flightNumber)
                );
        }
    }

    public function test_flight_search_is_limited_to_100_characters(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/flights?search='.str_repeat('a', 101))
            ->assertSessionHasErrors('search');
    }
}
