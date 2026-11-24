<?php

namespace App\Http\Requests;

use App\Models\Booking;
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
        $passportNumberRules = ['required', 'string', 'max:20'];
        $booking = $this->route('booking');

        if ($booking instanceof Booking) {
            $passportNumberRules[] = Rule::unique('passengers', 'passport_number')
                ->ignore($booking->passenger_id);
        }

        return [
            'passport_number'   => $passportNumberRules,
            'full_name'         => ['required', 'string', 'max:150'],
            'flight_id'         => ['required', 'integer', 'exists:flights,id'],
            'passenger_seat_id' => ['required', 'integer', 'exists:passenger_seats,id'],
            'baggage_fee'       => ['nullable', 'numeric', 'min:0'],
        ];
    }
}