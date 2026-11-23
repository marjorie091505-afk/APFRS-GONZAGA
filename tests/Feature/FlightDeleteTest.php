<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlightDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_and_delete_a_flight(): void
    {
        [$origin, $destination] = $this->createAirports();
        $flight = $this->createFlight($origin, $destination);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/flights')
            ->assertOk()
            ->assertSee('Delete');

        $this->delete(route('flights.destroy', $flight))
            ->assertRedirect('/flights')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('flights', ['id' => $flight->id]);
    }

    public function test_non_admin_cannot_delete_a_flight(): void
    {
        [$origin, $destination] = $this->createAirports();
        $flight = $this->createFlight($origin, $destination);

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->delete(route('flights.destroy', $flight))
            ->assertForbidden();

        $this->assertDatabaseHas('flights', ['id' => $flight->id]);
    }

    private function createAirports(): array
    {
        $origin = Airport::create([
            'code' => 'MNL',
            'name' => 'Manila Airport',
            'city' => 'Manila',
            'country' => 'Philippines',
        ]);
        $destination = Airport::create([
            'code' => 'CEB',
            'name' => 'Cebu Airport',
            'city' => 'Cebu',
            'country' => 'Philippines',
        ]);

        return [$origin, $destination];
    }

    private function createFlight(Airport $origin, Airport $destination): Flight
    {
        return Flight::create([
            'flight_number' => 'GA101',
            'origin_airport_id' => $origin->code,
            'destination_airport_id' => $destination->code,
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
    }
}
