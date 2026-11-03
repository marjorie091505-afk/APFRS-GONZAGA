<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // Add these two relationships:
    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id', 'id');
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class, 'flight_id', 'id');
    }
}