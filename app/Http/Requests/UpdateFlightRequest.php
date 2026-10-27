<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $flightId = $this->route('flight');

        return [
            'flight_number'          => ['required', 'string', 'max:10', Rule::unique('flights', 'flight_number')->ignore($flightId)],
            'origin_airport_id'      => ['required', 'integer', 'exists:airports,id'],
            'destination_airport_id' => ['required', 'integer', 'exists:airports,id', 'different:origin_airport_id'],
            'departure_time'         => ['required', 'date'],
            'base_fare'              => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'flight_number.unique'             => 'This flight number is already taken.',
            'destination_airport_id.different' => 'Destination must differ from origin.',
        ];
    }
}