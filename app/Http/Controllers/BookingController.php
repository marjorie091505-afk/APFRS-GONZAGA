<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::all();
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $flights = Flight::all();
        return view('bookings.create', compact('flights'));
    }

    public function store(Request $request)
    {
        // 1. Validate form
        $request->validate([
            'passenger_passport' => 'required|string',
            'passenger_name' => 'required|string',
            'flight_number' => 'required|string',
            'seat_number' => 'required|string',
            'seat_class' => 'required|string',
            'baggage_fee' => 'required|numeric|min:0',
        ]);

        $passport = strtoupper($request->passenger_passport); // Business Rule: Uppercase

        // 2. Business Rule: Check seat availability
        $seatTaken = PassengerSeat::where('flight_number', $request->flight_number)
                                  ->where('seat_number', $request->seat_number)
                                  ->where('is_available', false)
                                  ->exists();

        if ($seatTaken) {
            return back()->withErrors(['seat_number' => 'This seat is already booked! Please choose another.'])->withInput();
        }

        // 3. Create or find the passenger
        $passenger = Passenger::firstOrCreate(
            ['passport_number' => $passport],
            ['full_name' => $request->passenger_name]
        );

        // 4. Calculate total paid
        $flight = Flight::where('flight_number', $request->flight_number)->first();
        if (!$flight) {
            return back()->withErrors(['flight_number' => 'Flight not found.'])->withInput();
        }
        $totalPaid = $flight->base_fare + $request->baggage_fee;

        // 5. Create the booking (USING THE CORRECT COLUMN NAMES FROM YOUR DATABASE)
        $booking = Booking::create([
            'booking_reference' => 'BK-' . strtoupper(uniqid()),
            'passenger_id' => $passenger->id, // Database uses passenger_id
            'flight_id' => $flight->id,       // Database uses flight_id
            'seat_class' => $request->seat_class,
            'baggage_fee' => $request->baggage_fee,
            'total_paid' => $totalPaid,
        ]);

        // 6. Mark the seat as booked
        PassengerSeat::create([
            'flight_number' => $request->flight_number,
            'seat_number' => $request->seat_number,
            'is_available' => false,
            'booking_id' => $booking->id,
        ]);

        return redirect()->route('bookings.index')->with('success', 'Booking Confirmed! Total Paid: ₱' . number_format($totalPaid, 2));
    }
}