<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Flight;
use App\Services\DomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function __construct(private DomainService $service) {}

    public function index(): View
    {
        $bookings = Booking::with([
                'passenger',
                'flight.originAirport',
                'flight.destinationAirport',
                'seat',
            ])
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function create(): View
    {
        // THIS is the fix. We explicitly load availableSeats here.
        $flights = Flight::with(['originAirport', 'destinationAirport', 'availableSeats'])->get();
        return view('bookings.create', compact('flights'));
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        try {
            $this->service->createBooking($request->validated());

            return redirect()->route('bookings.index')
                ->with('success', 'Reservation confirmed successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['passenger_seat_id' => $e->getMessage()]);
        }
    }

    public function update(UpdateBookingRequest $request, $id): RedirectResponse
    {
        try {
            $booking = Booking::findOrFail($id);
            $this->service->updateBooking($booking, $request->validated());

            return redirect()->route('bookings.index')
                ->with('success', 'Booking updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['passenger_seat_id' => $e->getMessage()]);
        }
    }
}
