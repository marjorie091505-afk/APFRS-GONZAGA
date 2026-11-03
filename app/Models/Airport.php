<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    use HasFactory;

    // 1. Tell Laravel your custom Primary Key
    protected $primaryKey = 'airport_code';
    
    // 2. Tell Laravel it's not an auto-incrementing integer
    public $incrementing = false;
    protected $keyType = 'string';

    // 3. The fillable array
    protected $fillable = ['code', 'name', 'city', 'country'];

    public function departingFlights()
    {
        return $this->hasMany(Flight::class, 'origin_airport', 'airport_code');
    }

    public function arrivingFlights()
    {
        return $this->hasMany(Flight::class, 'destination_airport', 'airport_code');
    }
}