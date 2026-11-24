<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PassengerSeat extends Model
{
    use HasFactory;

    protected $fillable = ['flight_id', 'seat_number', 'seat_class', 'is_available'];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function setSeatNumberAttribute(string $value): void
    {
        $this->attributes['seat_number'] = strtoupper(trim($value));
    }

    public function flight(): BelongsTo
    {
        // Force Laravel to look for 'flight_id', not 'flight_number'
        return $this->belongsTo(Flight::class, 'flight_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'passenger_seat_id');
    }

    public function scopeAvailableForBooking(Builder $query): Builder
    {
        return $query
            ->where('is_available', true)
            ->whereNotExists(function (QueryBuilder $query) {
                $query->selectRaw('1')
                    ->from('bookings as reserved_bookings')
                    ->join('passenger_seats as reserved_seats', 'reserved_seats.id', '=', 'reserved_bookings.passenger_seat_id')
                    ->whereColumn('reserved_bookings.flight_id', 'passenger_seats.flight_id')
                    ->whereColumn('reserved_seats.seat_number', 'passenger_seats.seat_number');
            });
    }
}
