<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\PassengerSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FlightSeatGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_flight_generates_available_seats(): void
    {
        Airport::create([
            'code' => 'mnl',
            'name' => 'Manila Airport',
            'city' => 'Manila',
            'country' => 'Philippines',
        ]);
        Airport::create([
            'code' => 'ceb',
            'name' => 'Cebu Airport',
            'city' => 'Cebu',
            'country' => 'Philippines',
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->post('/flights', [
            'flight_number' => 'GA101',
            'origin_airport_id' => 'mnl',
            'destination_airport_id' => 'ceb',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'base_fare' => 2500,
        ])->assertRedirect('/flights');

        $this->assertDatabaseHas('airports', ['code' => 'MNL']);
        $this->assertDatabaseHas('airports', ['code' => 'CEB']);
        $this->assertDatabaseHas('flights', [
            'flight_number' => 'GA101',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
        ]);
        $this->assertDatabaseCount('passenger_seats', 140);
        $this->get('/flights')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('flights/index')
                ->where('flights.data.0.flight_number', 'GA101')
                ->where('flights.data.0.available_seats_count', 140)
            );
        $this->get('/bookings/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('bookings/create')
                ->where('flights.0.flight_number', 'GA101')
                ->where('flights.0.available_seats.0.seat_number', '1A')
            );
    }

    public function test_origin_and_destination_airports_must_be_different(): void
    {
        Airport::create([
            'code' => 'MNL',
            'name' => 'Manila Airport',
            'city' => 'Manila',
            'country' => 'Philippines',
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->post('/flights', [
            'flight_number' => 'GA101',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'MNL',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'base_fare' => 2500,
        ])
            ->assertSessionHasErrors('destination_airport_id');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_command_generates_seats_only_for_flights_without_seat_records(): void
    {
        Airport::create([
            'code' => 'MNL',
            'name' => 'Manila Airport',
            'city' => 'Manila',
            'country' => 'Philippines',
        ]);
        Airport::create([
            'code' => 'CEB',
            'name' => 'Cebu Airport',
            'city' => 'Cebu',
            'country' => 'Philippines',
        ]);

        $flightWithoutSeats = Flight::create([
            'flight_number' => 'GA101',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
        $flightWithSeats = Flight::create([
            'flight_number' => 'GA102',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDays(2),
            'base_fare' => 2500,
        ]);
        PassengerSeat::create([
            'flight_id' => $flightWithSeats->id,
            'seat_number' => '1A',
            'seat_class' => 'economy',
            'is_available' => true,
        ]);

        $this->artisan('flights:generate-missing-seats')
            ->expectsOutput('Generated seats for 1 flight(s).')
            ->assertExitCode(0);

        $this->assertSame(140, $flightWithoutSeats->passengerSeats()->count());
        $this->assertSame(1, $flightWithSeats->passengerSeats()->count());
    }

    public function test_regular_users_cannot_create_flights(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/flights/create')
            ->assertForbidden();

        $this->post('/flights', [])
            ->assertForbidden();
    }
}
