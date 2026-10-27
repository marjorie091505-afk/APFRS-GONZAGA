<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookingId = $this->route('booking');

        return [
            'booking_reference' => ['required', 'string', 'max:20', Rule::unique('bookings', 'booking_reference')->ignore($bookingId)],
            'passport_number'   => ['required', 'string', 'max:20'],
            'full_name'         => ['required', 'string', 'max:150'],
            'flight_id'         => ['required', 'integer', 'exists:flights,id'],
            'seat_class'        => ['required', 'string', 'in:Economy,Business,First'],
            'baggage_fee'       => ['nullable', 'numeric', 'min:0'],
            'seat_number'       => ['required', 'string', 'max:5'],
        ];
    }
}