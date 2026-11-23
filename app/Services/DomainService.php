<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\PassengerSeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DomainService
{
    public function createFlight(array $data): Flight
    {
        return DB::transaction(function () use ($data) {
            if (Airport::count() === 0) {
                Airport::create(['code' => 'MNL', 'name' => 'Ninoy Aquino', 'city' => 'Manila', 'country' => 'Philippines']);
                Airport::create(['code' => 'CEB', 'name' => 'Mactan Cebu', 'city' => 'Cebu', 'country' => 'Philippines']);
            } // ANO NI?

            $firstAirport = Airport::first();
            $secondAirport = Airport::skip(1)->first() ?? $firstAirport; // WHY U SKIP THE FIRST 1?

            $data['origin_airport_id'] = $firstAirport->id;
            $data['destination_airport_id'] = $secondAirport->id;

            $clean = $this->sanitizeFlightData($data);
            $flight = Flight::create($clean);
            $this->ensureSeatsForFlight($flight);

            return $flight->load(['originAirport', 'destinationAirport']);
        });
    }

    public function updateFlight(Flight $flight, array $data): Flight
    {
        return DB::transaction(function () use ($flight, $data) {
            $clean = $this->sanitizeFlightData($data);
            $flight->update($clean);
            return $flight->fresh(['originAirport', 'destinationAirport']);
        });
    }

    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // 1. Sanitize
            $passportNumber = strtoupper(trim($data['passport_number']));
            $fullName       = $this->stripTags(trim($data['full_name']));
            $email          = isset($data['email']) ? strtolower(trim($data['email'])) : null;
            $phone          = $this->formatPhone($data['phone'] ?? null);

            // 2. Passenger
            $passenger = Passenger::firstOrCreate(
                ['passport_number' => $passportNumber],
                ['full_name' => $fullName, 'email' => $email, 'phone' => $phone]
            );

            if (! $passenger->wasRecentlyCreated) {
                $passenger->update([
                    'full_name' => $fullName,
                    'email'     => $email ?? $passenger->email,
                    'phone'     => $phone ?? $passenger->phone,
                ]);
            }

            // 3. Flight & Seat
            $flight = Flight::findOrFail($data['flight_id']);
            $seat   = PassengerSeat::lockForUpdate()->findOrFail($data['passenger_seat_id']);

            // 4. CRITICAL FIX: Check using flight_id
            if ((int) $seat->flight_id !== (int) $flight->id) {
                throw new InvalidArgumentException('Selected seat does not belong to this flight.');
            }

            if (! $seat->is_available) {
                throw new InvalidArgumentException('Selected seat is already taken. Please choose another.');
            }

            // 5. Calculate Total
            $baseFare   = round((float) $flight->base_fare, 2);
            $baggageFee = round((float) ($data['baggage_fee'] ?? 0), 2);
            $totalPaid  = round($baseFare + $baggageFee, 2);

            // 6. Reserve Seat
            $seat->update(['is_available' => false]);

            // 7. Save Booking
            $booking = Booking::create([
                'booking_reference' => $this->generateBookingReference(),
                'passenger_id'      => $passenger->id,
                'flight_id'         => $flight->id,
                'passenger_seat_id' => $seat->id,
                'seat_class'        => $seat->seat_class,
                'baggage_fee'       => $baggageFee,
                'total_paid'        => $totalPaid,
            ]);

            return $booking->load(['passenger', 'flight.originAirport', 'flight.destinationAirport', 'seat']);
        });
    }

    public function updateBooking(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $passportNumber = strtoupper(trim($data['passport_number']));
            $fullName       = $this->stripTags(trim($data['full_name']));
            $email          = isset($data['email']) ? strtolower(trim($data['email'])) : null;
            $phone          = $this->formatPhone($data['phone'] ?? null);

            $booking->passenger->update([
                'passport_number' => $passportNumber,
                'full_name'       => $fullName,
                'email'           => $email,
                'phone'           => $phone,
            ]);

            $newSeatId = (int) $data['passenger_seat_id'];
            if ($newSeatId !== (int) $booking->passenger_seat_id) {
                $newSeat = PassengerSeat::lockForUpdate()->findOrFail($newSeatId);

                if ((int) $newSeat->flight_id !== (int) $booking->flight_id) {
                    throw new InvalidArgumentException('Selected seat does not belong to this flight.');
                }

                if (! $newSeat->is_available) {
                    throw new InvalidArgumentException('Selected seat is already taken.');
                }

                $booking->seat->update(['is_available' => true]);
                $newSeat->update(['is_available' => false]);
                $booking->passenger_seat_id = $newSeat->id;
            }

            $baggageFee = round((float) ($data['baggage_fee'] ?? 0), 2);
            $baseFare   = round((float) $booking->base_fare, 2);

            $booking->baggage_fee = $baggageFee;
            $booking->total_paid  = round($baseFare + $baggageFee, 2);
            $booking->status      = $data['status'] ?? $booking->status;
            $booking->save();

            return $booking->fresh(['passenger', 'flight.originAirport', 'flight.destinationAirport', 'seat']);
        });
    }

    private function sanitizeFlightData(array $data): array
    {
        return [
            'flight_number'          => strtoupper(trim($data['flight_number'])),
            'origin_airport_id'      => $data['origin_airport_id'],
            'destination_airport_id' => $data['destination_airport_id'],
            'departure_time'         => $data['departure_time'],
            'arrival_time'           => $data['arrival_time'],
            'base_fare'              => round((float) $data['base_fare'], 2),
        ];
    }

    private function stripTags(?string $value): ?string
    {
        return $value === null ? null : strip_tags(trim($value));
    }

    private function formatPhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') return null;
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11) {
            return sprintf('+63 (%s) %s-%s', substr($digits, 1, 3), substr($digits, 4, 3), substr($digits, 7, 4));
        }
        return $digits;
    }

    //ANO ANG PURPOSE?
    private function generateBookingReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(8));
        } while (Booking::where('booking_reference', $ref)->exists());
        return $ref;
    }

    public function ensureSeatsForFlight(Flight $flight): void
    {
        if ($flight->passengerSeats()->exists()) {
            return;
        }

        $layout = [
            ['class' => 'first',    'rows' => 2,  'letters' => ['A', 'B']],
            ['class' => 'business', 'rows' => 4,  'letters' => ['A', 'B', 'C', 'D']],
            ['class' => 'economy',  'rows' => 20, 'letters' => ['A', 'B', 'C', 'D', 'E', 'F']],
        ];

        $seats = [];
        $now   = now();

        foreach ($layout as $block) {
            for ($row = 1; $row <= $block['rows']; $row++) {
                foreach ($block['letters'] as $letter) {
                    $seats[] = [
                        'flight_id'    => $flight->id,
                        'seat_number'  => $row . $letter,
                        'seat_class'   => $block['class'],
                        'is_available' => true,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }
            }
        }
        PassengerSeat::insert($seats);
    }
}
