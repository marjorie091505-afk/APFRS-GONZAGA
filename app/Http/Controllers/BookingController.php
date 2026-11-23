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
                'passenger', // NEED FURTHER OPTIMIZATION: This eager loading is necessary to avoid N+1 queries, but we should consider if we can limit the fields retrieved for the passenger to only those needed for the view.
                'flight.originAirport',// NEED FURTHER OPTIMIZATION
                'flight.destinationAirport',// NEED FURTHER OPTIMIZATION
                'seat',// NEED FURTHER OPTIMIZATION
            ])
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function create(): View
    {
        // THIS is the fix. We explicitly load availableSeats here.
        $flights = Flight::with(['originAirport', 'destinationAirport', 'availableSeats'])->get();// NEED FURTHER OPTIMIZATION
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
            $booking = Booking::findOrFail($id);// NEED FURTHER OPTIMIZATION
            $this->service->updateBooking($booking, $request->validated());// NEED FURTHER OPTIMIZATION

            return redirect()->route('bookings.index')
                ->with('success', 'Booking updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['passenger_seat_id' => $e->getMessage()]);
        }
    }
}
