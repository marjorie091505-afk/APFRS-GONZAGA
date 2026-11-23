<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'flights' => Flight::count(),
            'bookings' => Booking::count(),
            'passengers' => Passenger::count(),
            'available_seats' => PassengerSeat::where('is_available', true)->count(),
            'revenue' => Booking::sum('total_paid'),
            'upcoming_flights' => Flight::where('departure_time', '>=', now())->count(),
        ];

        $recentBookings = Booking::with([
            'passenger',
            'flight.originAirport',
            'flight.destinationAirport',
        ])
            ->latest()
            ->take(5)
            ->get();

        $upcomingFlights = Flight::with([
            'originAirport',
            'destinationAirport',
        ])
            ->where('departure_time', '>=', now())
            ->orderBy('departure_time')
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentBookings', 'upcomingFlights'));
    }
}
