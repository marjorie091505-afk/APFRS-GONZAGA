<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassengerSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'flight_number',
        'seat_number',
        'is_available',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_reference');
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class, 'flight_number', 'flight_number');
    }
}