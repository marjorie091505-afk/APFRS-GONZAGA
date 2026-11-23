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
            'passport_number'   => ['required', 'string', 'max:20'],
            'full_name'         => ['required', 'string', 'max:255'],
            'email'             => ['nullable', 'email', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'flight_id'         => ['required', 'integer', 'exists:flights,id'],
            'passenger_seat_id' => ['required', 'integer', 'exists:passenger_seats,id'],
            'baggage_fee'       => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
