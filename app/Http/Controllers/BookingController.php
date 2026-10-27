<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Flight;
use App\Services\DomainService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function __construct(private DomainService $service) {}

    /** GET /bookings — paginated with eager-loaded relations */
    public function index(): JsonResponse
    {
        return response()->json($this->service->paginateBookings());
    }

    /** GET /bookings/create — dependency data */
    public function create(): JsonResponse
    {
        return response()->json([
            'flights' => Flight::with(['originAirport', 'destinationAirport'])
                ->orderBy('departure_time')
                ->get(),
        ]);
    }

    /** POST /bookings */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->service->createBooking($request->validated());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Booking created successfully.',
            'data'    => $booking,
        ], 201);
    }

    /** PUT/PATCH /bookings/{booking} */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->service->updateBooking($id, $request->validated());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Booking updated successfully.',
            'data'    => $booking,
        ]);
    }
}