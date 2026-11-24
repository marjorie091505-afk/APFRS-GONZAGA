<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\PassengerSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            ->assertInertia(fn (Assert $page) => $page
                ->component('bookings/create')
                ->where('flights.0.flight_number', 'GA101')
                ->where('flights.0.available_seats.0.seat_number', '1A')
            );
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

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/bookings', [
                'passport_number' => 'p123456',
                'full_name' => 'Test Passenger',
                'flight_id' => $flight->id,
                'passenger_seat_id' => $seat->id,
                'baggage_fee' => 500,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'passenger_seat_id' => $seat->id,
            'seat_class' => 'economy',
            'baggage_fee' => 500,
            'total_paid' => 3000,
        ]);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseHas('passengers', [
            'passport_number' => 'P123456',
            'full_name' => 'Test Passenger',
        ]);
        $this->assertFalse($seat->fresh()->is_available);

        $this->get('/bookings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('bookings/index')
                ->where('bookings.data.0.passenger.passport_number', 'P123456')
            );
    }

    public function test_same_seat_number_can_be_booked_on_different_flights(): void
    {
        $this->createAirportPair();
        $firstFlight = $this->createFlight('GA103');
        $secondFlight = $this->createFlight('GA104');
        $firstSeat = $this->createSeat($firstFlight, '1a');
        $secondSeat = $this->createSeat($secondFlight, '1A');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/bookings', $this->bookingData($firstFlight, $firstSeat, 'P123457'))
            ->assertRedirect('/bookings');
        $this->actingAs($user)
            ->post('/bookings', $this->bookingData($secondFlight, $secondSeat, 'P123458'))
            ->assertRedirect('/bookings');

        $this->assertDatabaseCount('bookings', 2);
        $this->assertFalse($firstSeat->fresh()->is_available);
        $this->assertFalse($secondSeat->fresh()->is_available);
        $this->assertCount(2, Booking::query()->pluck('booking_reference')->unique());
    }

    public function test_duplicate_seat_number_on_a_flight_cannot_be_reserved_twice(): void
    {
        $this->createAirportPair();
        $flight = $this->createFlight('GA105');
        $firstSeat = $this->createSeat($flight, '1A');
        $duplicateSeat = $this->createSeat($flight, '1A');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/bookings', $this->bookingData($flight, $firstSeat, 'P123459'))
            ->assertRedirect('/bookings');
        $this->actingAs($user)
            ->post('/bookings', $this->bookingData($flight, $firstSeat, 'P123459'))
            ->assertSessionHasErrors('passenger_seat_id');
        $this->actingAs($user)
            ->post('/bookings', $this->bookingData($flight, $duplicateSeat, 'P123460'))
            ->assertSessionHasErrors('passenger_seat_id');

        $this->actingAs($user)
            ->get('/bookings/create')
            ->assertInertia(fn (Assert $page) => $page
                ->where('flights.0.available_seats', [])
            );

        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('passengers', 1);
        $this->assertFalse($firstSeat->fresh()->is_available);
        $this->assertTrue($duplicateSeat->fresh()->is_available);
    }

    public function test_booking_rejects_a_seat_from_a_different_flight(): void
    {
        $this->createAirportPair();
        $flight = $this->createFlight('GA106');
        $otherFlight = $this->createFlight('GA107');
        $otherFlightSeat = $this->createSeat($otherFlight, '1A');

        $this->actingAs(User::factory()->create())
            ->post('/bookings', $this->bookingData($flight, $otherFlightSeat, 'P123461'))
            ->assertSessionHasErrors('passenger_seat_id');

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('passengers', 0);
        $this->assertTrue($otherFlightSeat->fresh()->is_available);
    }

    private function createAirportPair(): void
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
    }

    private function createFlight(string $flightNumber): Flight
    {
        return Flight::create([
            'flight_number' => $flightNumber,
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
    }

    private function createSeat(Flight $flight, string $seatNumber): PassengerSeat
    {
        return PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => $seatNumber,
            'seat_class' => 'economy',
            'is_available' => true,
        ]);
    }

    private function bookingData(Flight $flight, PassengerSeat $seat, string $passportNumber): array
    {
        return [
            'passport_number' => $passportNumber,
            'full_name' => 'Test Passenger',
            'flight_id' => $flight->id,
            'passenger_seat_id' => $seat->id,
            'baggage_fee' => 500,
        ];
    }
}
