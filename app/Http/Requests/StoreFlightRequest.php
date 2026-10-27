<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flight_number'          => ['required', 'string', 'max:10', 'unique:flights,flight_number'],
            'origin_airport_id'      => ['required', 'integer', 'exists:airports,id'],
            'destination_airport_id' => ['required', 'integer', 'exists:airports,id', 'different:origin_airport_id'],
            'departure_time'         => ['required', 'date', 'after:now'],
            'base_fare'              => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'flight_number.unique'            => 'This flight number is already registered.',
            'origin_airport_id.exists'        => 'The selected origin airport does not exist.',
            'destination_airport_id.different'=> 'Destination must differ from origin.',
            'departure_time.after'            => 'Departure time must be in the future.',
            'base_fare.min'                   => 'Base fare cannot be negative.',
        ];
    }
}