<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    
    // 2. Tell Laravel it's not an auto-incrementing integer
    public $incrementing = false;
    protected $keyType = 'string';

    // 3. The fillable array
    protected $fillable = ['code', 'name', 'city', 'country'];

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function departingFlights(): HasMany
    {
        return $this->hasMany(Flight::class, 'origin_airport_id', 'code');
    }

    public function arrivingFlights(): HasMany
    {
        return $this->hasMany(Flight::class, 'destination_airport_id', 'code');
    }
}