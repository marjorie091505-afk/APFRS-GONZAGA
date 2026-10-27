<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DomainService
{
    /* ============================================================
     |  FLIGHT
     * ============================================================ */

    public function paginateFlights(int $perPage = 10): LengthAwarePaginator
    {
        return Flight::with(['originAirport', 'destinationAirport'])
            ->orderBy('departure_time')
            ->paginate($perPage);
    }

    public function createFlight(array $data): Flight
    {
        $payload = $this->sanitizeFlight($data);

        return DB::transaction(fn () => Flight::create($payload));
    }

    public function updateFlight(int $id, array $data): Flight
    {
        $flight  = Flight::findOrFail($id);
        $payload = $this->sanitizeFlight($data);

        return DB::transaction(function () use ($flight, $payload) {
            $flight->update($payload);
            return $flight->fresh(['originAirport', 'destinationAirport']);
        });
    }

    /* ============================================================
     |  BOOKING
     * ============================================================ */

    public function paginateBookings(int $perPage = 10): LengthAwarePaginator
    {
        return Booking::with(['passenger', 'flight.originAirport', 'flight.destinationAirport', 'seats'])
            ->latest()
            ->paginate($perPage);
    }

    public function createBooking(array $data): Booking
    {
        $payload = $this->sanitizeBooking($data);

        return DB::transaction(function () use ($payload) {
            $flight = Flight::findOrFail($payload['flight_id']);

            $this->assertSeatAvailable($flight->id, $payload['seat_number']);

            // Upsert passenger by passport (uppercased in sanitize)
            $passenger = Passenger::updateOrCreate(
                ['passport_number' => $payload['passport_number']],
                ['full_name'       => $payload['full_name']]
            );

            $totalPaid = $this->calculateTotalPaid($flight->base_fare, $payload['baggage_fee']);

            $booking = Booking::create([
                'booking_reference' => $payload['booking_reference'],
                'passenger_id'      => $passenger->id,
                'flight_id'         => $flight->id,
                'seat_class'        => $payload['seat_class'],
                'baggage_fee'       => $payload['baggage_fee'],
                'total_paid'        => $totalPaid,
            ]);

            PassengerSeat::create([
                'booking_id'  => $booking->id,
                'seat_number' => $payload['seat_number'],
            ]);

            return $booking->load(['passenger', 'flight', 'seats']);
        });
    }

    public function updateBooking(int $id, array $data): Booking
    {
        $booking = Booking::findOrFail($id);
        $payload = $this->sanitizeBooking($data);

        return DB::transaction(function () use ($booking, $payload) {
            $flight = Flight::findOrFail($payload['flight_id']);

            // Seat availability check excludes this booking's current seat
            $this->assertSeatAvailable(
                $flight->id,
                $payload['seat_number'],
                $booking->id
            );

            $passenger = Passenger::updateOrCreate(
                ['passport_number' => $payload['passport_number']],
                ['full_name'       => $payload['full_name']]
            );

            $totalPaid = $this->calculateTotalPaid($flight->base_fare, $payload['baggage_fee']);

            $booking->update([
                'booking_reference' => $payload['booking_reference'],
                'passenger_id'      => $passenger->id,
                'flight_id'         => $flight->id,
                'seat_class'        => $payload['seat_class'],
                'baggage_fee'       => $payload['baggage_fee'],
                'total_paid'        => $totalPaid,
            ]);

            // Replace seat assignment
            $booking->seats()->delete();
            PassengerSeat::create([
                'booking_id'  => $booking->id,
                'seat_number' => $payload['seat_number'],
            ]);

            return $booking->fresh(['passenger', 'flight', 'seats']);
        });
    }

    /* ============================================================
     |  BUSINESS RULES / SANITIZATION
     * ============================================================ */

    private function sanitizeFlight(array $data): array
    {
        return [
            'flight_number'          => strtoupper(trim(strip_tags($data['flight_number']))),
            'origin_airport_id'      => (int) $data['origin_airport_id'],
            'destination_airport_id' => (int) $data['destination_airport_id'],
            'departure_time'         => $data['departure_time'],
            'base_fare'              => round((float) $data['base_fare'], 2),
        ];
    }

    private function sanitizeBooking(array $data): array
    {
        return [
            'booking_reference' => strtoupper(trim(strip_tags($data['booking_reference']))),
            'passport_number'   => strtoupper(trim(strip_tags($data['passport_number']))),
            'full_name'         => trim(strip_tags($data['full_name'])),
            'flight_id'         => (int) $data['flight_id'],
            'seat_class'        => trim(strip_tags($data['seat_class'])),
            'baggage_fee'       => round((float) ($data['baggage_fee'] ?? 0), 2),
            'seat_number'       => strtoupper(trim(strip_tags($data['seat_number']))),
        ];
    }

    private function assertSeatAvailable(int $flightId, string $seatNumber, ?int $ignoreBookingId = null): void
    {
        $query = PassengerSeat::where('seat_number', $seatNumber)
            ->whereHas('booking', function ($q) use ($flightId, $ignoreBookingId) {
                $q->where('flight_id', $flightId);
                if ($ignoreBookingId) {
                    $q->where('id', '!=', $ignoreBookingId);
                }
            });

        if ($query->exists()) {
            throw new InvalidArgumentException(
                "Seat {$seatNumber} is already taken on this flight."
            );
        }
    }

    private function calculateTotalPaid(float $baseFare, float $baggageFee): float
    {
        return round($baseFare + $baggageFee, 2);
    }
}