<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'origin_airport_id',
        'destination_airport_id',
        'departure_time',
        'base_fare',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'departure_time' => 'datetime',
    ];

    // Add 'code' as the third parameter so Laravel knows the airports table uses 'code' as the primary key
    public function originAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin_airport_id', 'code');
    }

    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_airport_id', 'code');
    }

    public function passengerSeats(): HasMany
    {
        return $this->hasMany(PassengerSeat::class, 'flight_number', 'flight_number');
    }

    public function availableSeats(): HasMany
    {
        return $this->hasMany(PassengerSeat::class, 'flight_number', 'flight_number')->where('is_available', true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'flight_number', 'flight_number');
    }
}