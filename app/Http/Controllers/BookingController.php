<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Flight;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private BookingService $service) {}

    public function index(): Response
    {
        $query = Booking::query()
            ->select([
                'bookings.id',
                'bookings.booking_reference',
                'bookings.passenger_id',
                'bookings.flight_id',
                'bookings.passenger_seat_id',
                'bookings.seat_class',
                'bookings.baggage_fee',
                'bookings.total_paid',
                'bookings.created_at',
            ])
            ->with([
                'passenger:id,passport_number,full_name',
                'flight:id,flight_number,origin_airport_id,destination_airport_id,departure_time,base_fare',
                'flight.originAirport:code,name,city,country',
                'flight.destinationAirport:code,name,city,country',
                'seat:id,flight_id,seat_number,seat_class,is_available',
            ])
            ->latest('bookings.created_at');

        if (! auth()->user()->is_admin) {
            $query->where('bookings.user_id', auth()->id());
        }

        $bookings = $query->paginate(10);

        return Inertia::render('bookings/index', compact('bookings'));
    }

    public function create(): Response
    {
        $flights = Flight::query()
            ->select(['id', 'flight_number', 'origin_airport_id', 'destination_airport_id', 'departure_time', 'base_fare'])
            ->with([
                'originAirport:code,name,city,country',
                'destinationAirport:code,name,city,country',
                'availableSeats:id,flight_id,seat_number,seat_class,is_available',
            ])
            ->where('departure_time', '>=', now())
            ->orderBy('departure_time')
            ->get();

        return Inertia::render('bookings/create', compact('flights'));
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        try {
            $this->service->createBooking($request->validated(), auth()->id());

            return redirect()->route('bookings.index')
                ->with('success', 'Reservation confirmed successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['passenger_seat_id' => $e->getMessage()]);
        }
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        try {
            abort_unless(auth()->user()->is_admin, 403);
            $this->service->updateBooking($booking, $request->validated());

            return redirect()->route('bookings.index')
                ->with('success', 'Booking updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['passenger_seat_id' => $e->getMessage()]);
        }
    }

    public function edit(Booking $booking): Response
    {
        abort_unless(auth()->user()->is_admin, 403);

        $booking->load([
            'passenger:id,passport_number,full_name',
            'seat:id,flight_id,seat_number,seat_class,is_available',
            'flight:id,flight_number,origin_airport_id,destination_airport_id,departure_time,base_fare',
            'flight.originAirport:code,name,city,country',
            'flight.destinationAirport:code,name,city,country',
        ]);
        $flights = Flight::query()
            ->select(['id', 'flight_number', 'origin_airport_id', 'destination_airport_id', 'departure_time', 'base_fare'])
            ->with([
                'originAirport:code,name,city,country',
                'destinationAirport:code,name,city,country',
                'passengerSeats:id,flight_id,seat_number,seat_class,is_available',
            ])
            ->orderBy('departure_time')
            ->get();

        return Inertia::render('bookings/edit', compact('booking', 'flights'));
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $this->service->deleteBooking($booking);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }
}
