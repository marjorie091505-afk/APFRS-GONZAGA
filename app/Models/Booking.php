<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'passenger_id',
        'flight_id',
        'seat_class',
        'baggage_fee',
        'total_paid',
    ];

    protected $casts = [
        'baggage_fee' => 'decimal:2',
        'total_paid'  => 'decimal:2',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(PassengerSeat::class);
    }
}