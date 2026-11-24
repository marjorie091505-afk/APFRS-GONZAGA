<?php

namespace Tests\Feature;

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_edit_and_delete_booking_actions(): void
    {
        $booking = $this->createBooking();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/bookings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('bookings/index')
                ->where('auth.user.is_admin', true)
                ->where('bookings.data.0.booking_reference', $booking->booking_reference)
            );

        $this->get(route('bookings.edit', $booking))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('bookings/edit'));

        $this->delete(route('bookings.destroy', $booking))
            ->assertRedirect('/bookings')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertDatabaseHas('passenger_seats', ['id' => $booking->passenger_seat_id, 'is_available' => true]);
    }

    public function test_passengers_only_see_their_own_bookings_while_admins_see_all(): void
    {
        $passenger = User::factory()->create();
        $otherPassenger = User::factory()->create();
        $this->createBooking($passenger, 'BK-OWNER001', 'GA201', 'P120001');
        $this->createBooking($otherPassenger, 'BK-OWNER002', 'GA202', 'P120002');

        $this->actingAs($passenger)
            ->get('/bookings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('bookings/index')
                ->has('bookings.data', 1)
                ->where('bookings.data.0.booking_reference', 'BK-OWNER001')
            );

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/bookings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('bookings/index')
                ->has('bookings.data', 2)
            );
    }

    public function test_admin_can_update_booking_and_server_calculates_total_and_releases_old_seat(): void
    {
        $booking = $this->createBooking();
        $flight = Flight::create([
            'flight_number' => 'GA102',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDays(2),
            'base_fare' => 4000,
        ]);
        $newSeat = PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => '2A',
            'seat_class' => 'business',
            'is_available' => true,
        ]);
        $oldSeatId = $booking->passenger_seat_id;

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('bookings.update', $booking), [
                'passport_number' => 'p7654321',
                'full_name' => 'Updated Passenger',
                'flight_id' => $flight->id,
                'passenger_seat_id' => $newSeat->id,
                'baggage_fee' => 300,
                'total_paid' => 1,
            ])
            ->assertRedirect('/bookings');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'flight_id' => $flight->id,
            'passenger_seat_id' => $newSeat->id,
            'seat_class' => 'business',
            'baggage_fee' => 300,
            'total_paid' => 4300,
        ]);
        $this->assertDatabaseHas('passengers', [
            'id' => $booking->passenger_id,
            'passport_number' => 'P7654321',
            'full_name' => 'Updated Passenger',
        ]);
        $this->assertDatabaseHas('passenger_seats', ['id' => $oldSeatId, 'is_available' => true]);
        $this->assertDatabaseHas('passenger_seats', ['id' => $newSeat->id, 'is_available' => false]);
    }

    public function test_booking_update_rejects_a_seat_from_another_flight(): void
    {
        $booking = $this->createBooking();
        $otherFlight = Flight::create([
            'flight_number' => 'GA103',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDays(2),
            'base_fare' => 4000,
        ]);
        $otherSeat = PassengerSeat::create([
            'flight_id' => $otherFlight->id,
            'seat_number' => '2A',
            'seat_class' => 'economy',
            'is_available' => true,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('bookings.update', $booking), [
                'passport_number' => 'P123456',
                'full_name' => 'Test Passenger',
                'flight_id' => $booking->flight_id,
                'passenger_seat_id' => $otherSeat->id,
                'baggage_fee' => 0,
            ])
            ->assertSessionHasErrors('passenger_seat_id');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'flight_id' => $booking->flight_id,
            'passenger_seat_id' => $booking->passenger_seat_id,
        ]);
        $this->assertTrue($otherSeat->fresh()->is_available);
    }

    public function test_booking_update_rejects_a_seat_number_already_reserved_on_the_flight(): void
    {
        $booking = $this->createBooking();
        $occupiedSeat = PassengerSeat::create([
            'flight_id' => $booking->flight_id,
            'seat_number' => '1B',
            'seat_class' => 'economy',
            'is_available' => false,
        ]);
        $otherPassenger = Passenger::create([
            'passport_number' => 'P120003',
            'full_name' => 'Other Passenger',
        ]);
        Booking::create([
            'booking_reference' => 'BK-OCCUPIED1',
            'passenger_id' => $otherPassenger->id,
            'flight_id' => $booking->flight_id,
            'passenger_seat_id' => $occupiedSeat->id,
            'seat_class' => 'economy',
            'baggage_fee' => 0,
            'total_paid' => 2500,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('bookings.update', $booking), [
                'passport_number' => 'P123456',
                'full_name' => 'Test Passenger',
                'flight_id' => $booking->flight_id,
                'passenger_seat_id' => $occupiedSeat->id,
                'baggage_fee' => 0,
            ])
            ->assertSessionHasErrors('passenger_seat_id');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'passenger_seat_id' => $booking->passenger_seat_id,
        ]);
        $this->assertFalse($occupiedSeat->fresh()->is_available);
    }

    public function test_non_admin_cannot_delete_booking(): void
    {
        $booking = $this->createBooking();

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('bookings.edit', $booking))
            ->assertForbidden();

        $passenger = User::factory()->create(['is_admin' => false]);
        $this->actingAs($passenger)
            ->put(route('bookings.update', $booking), [])
            ->assertForbidden();

        $this->actingAs($passenger)
            ->delete(route('bookings.destroy', $booking))
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    private function createBooking(
        ?User $owner = null,
        string $reference = 'BK-TEST001',
        string $flightNumber = 'GA101',
        string $passportNumber = 'P123456'
    ): Booking
    {
        $origin = Airport::firstOrCreate(
            ['code' => 'MNL'],
            ['name' => 'Manila Airport', 'city' => 'Manila', 'country' => 'Philippines']
        );
        $destination = Airport::firstOrCreate(
            ['code' => 'CEB'],
            ['name' => 'Cebu Airport', 'city' => 'Cebu', 'country' => 'Philippines']
        );
        $flight = Flight::create([
            'flight_number' => $flightNumber,
            'origin_airport_id' => $origin->code,
            'destination_airport_id' => $destination->code,
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
        $seat = PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => '1A',
            'seat_class' => 'economy',
            'is_available' => false,
        ]);
        $passenger = Passenger::create([
            'passport_number' => $passportNumber,
            'full_name' => 'Test Passenger',
        ]);

        return Booking::create([
            'booking_reference' => $reference,
            'passenger_id' => $passenger->id,
            'user_id' => $owner?->id,
            'flight_id' => $flight->id,
            'passenger_seat_id' => $seat->id,
            'seat_class' => 'economy',
            'baggage_fee' => 0,
            'total_paid' => 2500,
        ]);
    }
}
