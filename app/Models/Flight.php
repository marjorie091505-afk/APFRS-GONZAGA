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
        'departure_time' => 'datetime',
        'base_fare'      => 'decimal:2',
    ];

    public function originAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin_airport_id');
    }

    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_airport_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}