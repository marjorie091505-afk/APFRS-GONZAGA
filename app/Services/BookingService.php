<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BookingService
{
    public function createBooking(array $data, ?int $userId = null): Booking
    {
        return DB::transaction(function () use ($data, $userId) {
            $flight = Flight::query()
                ->lockForUpdate()
                ->findOrFail($data['flight_id']);

            if ($flight->departure_time->isPast()) {
                throw new InvalidArgumentException('Cannot book a flight that has already departed.');
            }

            $seat = PassengerSeat::query()
                ->lockForUpdate()
                ->findOrFail($data['passenger_seat_id']);

            if ((int) $seat->flight_id !== (int) $flight->id) {
                throw new InvalidArgumentException('Selected seat does not belong to this flight.');
            }

            if (! $seat->is_available || $this->seatNumberIsOccupied((int) $flight->id, $seat->seat_number)) {
                throw new InvalidArgumentException('Selected seat is already taken. Please choose another.');
            }

            $passenger = Passenger::firstOrCreate(
                ['passport_number' => strtoupper(trim($data['passport_number']))],
                ['full_name' => strip_tags(trim($data['full_name']))]
            );

            if (! $passenger->wasRecentlyCreated) {
                $passenger->update(['full_name' => strip_tags(trim($data['full_name']))]);
            }

            $baseFare = round((float) $flight->base_fare, 2);
            $baggageFee = round((float) ($data['baggage_fee'] ?? 0), 2);

            $seat->update(['is_available' => false]);

            $booking = Booking::create([
                'booking_reference' => $this->generateBookingReference(),
                'passenger_id' => $passenger->id,
                'user_id' => $userId,
                'flight_id' => $flight->id,
                'passenger_seat_id' => $seat->id,
                'seat_class' => $seat->seat_class,
                'baggage_fee' => $baggageFee,
                'total_paid' => round($baseFare + $baggageFee, 2),
            ]);

            return $booking;
        });
    }

    public function updateBooking(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->getKey());

            $flightIds = array_values(array_unique([(int) $booking->flight_id, (int) $data['flight_id']]));
            sort($flightIds);

            $flights = Flight::query()
                ->whereIn('id', $flightIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $flight = $flights->get((int) $data['flight_id']);

            if (! $flight) {
                throw new InvalidArgumentException('Selected flight is no longer available.');
            }

            $newSeat = PassengerSeat::query()
                ->lockForUpdate()
                ->findOrFail($data['passenger_seat_id']);

            if ((int) $newSeat->flight_id !== (int) $flight->id) {
                throw new InvalidArgumentException('Selected seat does not belong to this flight.');
            }

            $isCurrentSeat = (int) $booking->passenger_seat_id === (int) $newSeat->id;
            $seatNumberIsOccupied = $this->seatNumberIsOccupied(
                (int) $flight->id,
                $newSeat->seat_number,
                (int) $booking->id
            );

            if ($seatNumberIsOccupied || (! $isCurrentSeat && ! $newSeat->is_available)) {
                throw new InvalidArgumentException('Selected seat is already taken.');
            }

            $currentSeat = null;
            if ($booking->passenger_seat_id !== null) {
                $currentSeat = $isCurrentSeat
                    ? $newSeat
                    : PassengerSeat::query()->lockForUpdate()->findOrFail($booking->passenger_seat_id);
            }

            if ($currentSeat && ! $isCurrentSeat) {
                $currentSeat->update([
                    'is_available' => ! $this->seatNumberIsOccupied(
                        (int) $currentSeat->flight_id,
                        $currentSeat->seat_number,
                        (int) $booking->id
                    ),
                ]);
            }

            $newSeat->update(['is_available' => false]);

            $passenger = Passenger::query()
                ->lockForUpdate()
                ->findOrFail($booking->passenger_id);
            $passenger->update([
                'passport_number' => strtoupper(trim($data['passport_number'])),
                'full_name' => strip_tags(trim($data['full_name'])),
            ]);

            $baggageFee = round((float) ($data['baggage_fee'] ?? 0), 2);
            $booking->update([
                'flight_id' => $flight->id,
                'passenger_seat_id' => $newSeat->id,
                'seat_class' => $newSeat->seat_class,
                'baggage_fee' => $baggageFee,
                'total_paid' => round((float) $flight->base_fare + $baggageFee, 2),
            ]);

            return $booking->refresh();
        });
    }

    public function deleteBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->getKey());

            Flight::query()
                ->lockForUpdate()
                ->findOrFail($booking->flight_id);

            if ($booking->passenger_seat_id !== null) {
                $seat = PassengerSeat::query()
                    ->lockForUpdate()
                    ->findOrFail($booking->passenger_seat_id);

                $seat->update([
                    'is_available' => ! $this->seatNumberIsOccupied(
                        (int) $booking->flight_id,
                        $seat->seat_number,
                        (int) $booking->id
                    ),
                ]);
            }

            $booking->delete();
        });
    }

    private function seatNumberIsOccupied(int $flightId, string $seatNumber, ?int $exceptBookingId = null): bool
    {
        return Booking::query()
            ->where('flight_id', $flightId)
            ->when($exceptBookingId !== null, fn ($query) => $query->where('id', '!=', $exceptBookingId))
            ->whereHas('seat', fn ($query) => $query->where('seat_number', $seatNumber))
            ->exists();
    }

    private function generateBookingReference(): string
    {
        do {
            $reference = 'BK-'.Str::upper(Str::random(8));
        } while (Booking::query()->where('booking_reference', $reference)->exists());

        return $reference;
    }
}
