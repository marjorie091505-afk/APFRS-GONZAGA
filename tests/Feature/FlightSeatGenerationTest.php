<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlightSeatGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_flight_generates_available_seats(): void
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
        $this->actingAs(User::factory()->create());

        $this->post('/flights', [
            'flight_number' => 'GA101',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'base_fare' => 2500,
        ])->assertRedirect('/flights');

        $this->assertDatabaseCount('passenger_seats', 140);
        $this->get('/bookings/create')
            ->assertOk()
            ->assertSee('Seat 1A (Economy)');
    }
}
