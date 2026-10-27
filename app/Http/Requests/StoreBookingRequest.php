<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_reference' => ['required', 'string', 'max:20', 'unique:bookings,booking_reference'],
            'passport_number'   => ['required', 'string', 'max:20'],
            'full_name'         => ['required', 'string', 'max:150'],
            'flight_id'         => ['required', 'integer', 'exists:flights,id'],
            'seat_class'        => ['required', 'string', 'in:Economy,Business,First'],
            'baggage_fee'       => ['nullable', 'numeric', 'min:0'],
            'seat_number'       => ['required', 'string', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_reference.unique' => 'Booking reference already exists.',
            'flight_id.exists'         => 'The selected flight does not exist.',
            'seat_class.in'            => 'Seat class must be Economy, Business, or First.',
            'seat_number.required'     => 'Please assign a seat number.',
        ];
    }
}