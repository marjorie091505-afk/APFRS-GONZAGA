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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guests_to_login()
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_root_redirects_authenticated_users_to_the_dashboard()
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect('/dashboard');
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('upcomingBookings')
                ->missing('stats')
            );
    }

    public function test_admin_dashboard_stat_cards_link_to_their_relevant_pages()
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.flights', 0)
                ->where('stats.bookings', 0)
                ->where('stats.passengers', 0)
                ->where('stats.available_seats', 0)
                ->where('stats.upcoming_flights', 0)
                ->where('stats.revenue', 0)
                ->has('recentBookings', 0)
                ->has('upcomingFlights', 0)
            );
    }

    public function test_regular_users_see_stat_cards_without_admin_links()
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('upcomingBookings')
                ->missing('stats')
            );
    }

    public function test_admin_dashboard_statistics_use_database_records(): void
    {
        Airport::create(['code' => 'MNL', 'name' => 'Manila Airport', 'city' => 'Manila', 'country' => 'Philippines']);
        Airport::create(['code' => 'CEB', 'name' => 'Cebu Airport', 'city' => 'Cebu', 'country' => 'Philippines']);
        $flight = Flight::create([
            'flight_number' => 'GA901',
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
        PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => '1B',
            'seat_class' => 'economy',
            'is_available' => true,
        ]);
        $passenger = Passenger::create([
            'passport_number' => 'P901',
            'full_name' => 'Dashboard Passenger',
        ]);
        Booking::create([
            'booking_reference' => 'BK-DASH901',
            'passenger_id' => $passenger->id,
            'flight_id' => $flight->id,
            'passenger_seat_id' => PassengerSeat::where('seat_number', '1B')->value('id'),
            'seat_class' => 'economy',
            'baggage_fee' => 500,
            'total_paid' => 3000,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.flights', 1)
                ->where('stats.bookings', 1)
                ->where('stats.passengers', 1)
                ->where('stats.available_seats', 1)
                ->where('stats.upcoming_flights', 1)
                ->where('stats.revenue', 3000)
                ->where('recentBookings.0.flight.flight_number', 'GA901')
                ->where('recentBookings.0.flight.origin_airport.name', 'Manila Airport')
                ->where('upcomingFlights.0.destination_airport.name', 'Cebu Airport')
            );
    }

    public function test_passenger_dashboard_shows_only_their_upcoming_reservations(): void
    {
        Airport::create(['code' => 'MNL', 'name' => 'Manila Airport', 'city' => 'Manila', 'country' => 'Philippines']);
        Airport::create(['code' => 'CEB', 'name' => 'Cebu Airport', 'city' => 'Cebu', 'country' => 'Philippines']);
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $flight = Flight::create([
            'flight_number' => 'GA902',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->addDay(),
            'base_fare' => 2500,
        ]);
        $seat = PassengerSeat::create([
            'flight_id' => $flight->id,
            'seat_number' => '2A',
            'seat_class' => 'economy',
            'is_available' => false,
        ]);
        $passenger = Passenger::create([
            'passport_number' => 'P902',
            'full_name' => 'Scheduled Passenger',
        ]);
        Booking::create([
            'booking_reference' => 'BK-DASH902',
            'passenger_id' => $passenger->id,
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'passenger_seat_id' => $seat->id,
            'seat_class' => 'economy',
            'baggage_fee' => 0,
            'total_paid' => 2500,
        ]);
        Booking::create([
            'booking_reference' => 'BK-DASH903',
            'passenger_id' => $passenger->id,
            'user_id' => $otherUser->id,
            'flight_id' => $flight->id,
            'passenger_seat_id' => null,
            'seat_class' => 'economy',
            'baggage_fee' => 0,
            'total_paid' => 2500,
        ]);
        $pastFlight = Flight::create([
            'flight_number' => 'GA903',
            'origin_airport_id' => 'MNL',
            'destination_airport_id' => 'CEB',
            'departure_time' => now()->subDay(),
            'base_fare' => 2500,
        ]);
        Booking::create([
            'booking_reference' => 'BK-DASH904',
            'passenger_id' => $passenger->id,
            'user_id' => $user->id,
            'flight_id' => $pastFlight->id,
            'passenger_seat_id' => null,
            'seat_class' => 'economy',
            'baggage_fee' => 0,
            'total_paid' => 2500,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('upcomingBookings', 1)
                ->where('upcomingBookings.0.flight.flight_number', 'GA902')
                ->where('upcomingBookings.0.flight.origin_airport.name', 'Manila Airport')
                ->where('upcomingBookings.0.flight.destination_airport.name', 'Cebu Airport')
                ->has('upcomingBookings.0.flight.departure_time')
                ->where('upcomingBookings.0.seat.seat_number', '2A')
            );
    }
}
