<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $now = now();

        if (! $user->is_admin) {
            $upcomingBookings = Booking::query()
                ->select('bookings.*')
                ->join('flights', 'flights.id', '=', 'bookings.flight_id')
                ->where('bookings.user_id', $user->getKey())
                ->where('flights.departure_time', '>=', $now)
                ->with([
                    'flight:id,flight_number,origin_airport_id,destination_airport_id,departure_time',
                    'flight.originAirport:code,name,city',
                    'flight.destinationAirport:code,name,city',
                    'seat:id,flight_id,seat_number,seat_class',
                ])
                ->orderBy('flights.departure_time')
                ->limit(5)
                ->get();

            return Inertia::render('dashboard', [
                'upcomingBookings' => $upcomingBookings,
            ]);
        }

        $stats = [
            'flights' => Flight::count(),
            'bookings' => Booking::count(),
            'passengers' => Passenger::count(),
            'available_seats' => PassengerSeat::query()->availableForBooking()->count(),
            'revenue' => Booking::sum('total_paid'),
            'upcoming_flights' => Flight::where('departure_time', '>=', $now)->count(),
        ];

        $recentBookings = Booking::with([
            'passenger:id,full_name',
            'flight:id,flight_number,origin_airport_id,destination_airport_id,departure_time',
            'flight.originAirport:code,name,city',
            'flight.destinationAirport:code,name,city',
        ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $upcomingFlights = Flight::with([
            'originAirport:code,name,city',
            'destinationAirport:code,name,city',
        ])
            ->where('departure_time', '>=', $now)
            ->orderBy('departure_time')
            ->limit(5)
            ->get();

        return Inertia::render('dashboard', compact('stats', 'recentBookings', 'upcomingFlights'));
    }
}
