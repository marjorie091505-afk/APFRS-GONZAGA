<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PassengerSeat extends Model
{
    use HasFactory;

    protected $fillable = ['flight_id', 'seat_number', 'seat_class', 'is_available'];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function flight(): BelongsTo
    {
        // Force Laravel to look for 'flight_id', not 'flight_number'
        return $this->belongsTo(Flight::class, 'flight_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'passenger_seat_id');
    }
}
