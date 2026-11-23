<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Flight;
use App\Models\PassengerSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCreationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_form_loads_available_seats_using_flight_id(): void
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

        $flight = Flight::create([
            'flight_number' => 'GA101',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);

        PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => '1A',
            'seat_class' => 'economy',
            'is_available' => true,
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/bookings/create')
            ->assertOk()
            ->assertSee('GA101')
            ->assertSee('Seat 1A (Economy)');
    }

    public function test_booking_submission_saves_and_reserves_the_selected_seat(): void
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

        $flight = Flight::create([
            'flight_number' => 'GA102',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
        $seat = PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => '1A',
            'seat_class' => 'economy',
            'is_available' => true,
        ]);

        $this->actingAs(User::factory()->create())
            ->post('/bookings', [
                'passport_number' => 'P123456',
                'full_name' => 'Test Passenger',
                'flight_id' => $flight->id,
                'passenger_seat_id' => $seat->id,
                'baggage_fee' => 500,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'flight_id' => $flight->id,
            'passenger_seat_id' => $seat->id,
            'seat_class' => 'economy',
            'total_paid' => 3000,
        ]);
        $this->assertFalse($seat->fresh()->is_available);

        $this->get('/bookings')->assertOk()->assertSee('P123456');
    }
}
