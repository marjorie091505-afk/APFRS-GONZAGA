@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <h1 class="text-2xl font-bold mb-6">Edit Booking</h1>

    <form action="{{ route('bookings.update', $booking) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Passport Number</label>
                <input type="text" name="passport_number"
                       value="{{ old('passport_number', $booking->passenger->passport_number) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                @error('passport_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="full_name"
                       value="{{ old('full_name', $booking->passenger->full_name) }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                @error('full_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Flight</label>
            <select name="flight_id" id="edit-flight"
                    class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                @foreach($flights as $flight)
                    <option value="{{ $flight->id }}"
                            {{ old('flight_id', $booking->flight_id) == $flight->id ? 'selected' : '' }}>
                        {{ $flight->flight_number }}
                        ({{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Seat</label>
            <select name="passenger_seat_id" id="edit-seat"
                    class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                @foreach($flights as $flight)
                    @foreach($flight->passengerSeats as $seat)
                        @if($seat->is_available || $seat->id == $booking->passenger_seat_id)
                            <option value="{{ $seat->id }}"
                                    data-flight-id="{{ $flight->id }}"
                                    {{ old('passenger_seat_id', $booking->passenger_seat_id) == $seat->id ? 'selected' : '' }}>
                                {{ $flight->flight_number }} - Seat {{ $seat->seat_number }} ({{ ucfirst($seat->seat_class) }})
                            </option>
                        @endif
                    @endforeach
                @endforeach
            </select>
            @error('passenger_seat_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Baggage Fee (₱)</label>
            <input type="number" name="baggage_fee" step="0.01" min="0"
                   value="{{ old('baggage_fee', $booking->baggage_fee) }}"
                   class="w-full border border-gray-300 rounded-md px-3 py-2">
            @error('baggage_fee') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Save Changes
            </button>
            <a href="{{ route('bookings.index') }}" class="border border-gray-300 px-4 py-2 rounded-md hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const flight = document.getElementById('edit-flight');
    const seat = document.getElementById('edit-seat');

    function filterSeats() {
        Array.from(seat.options).forEach(function (option) {
            option.hidden = option.dataset.flightId !== flight.value;
        });

        const selected = seat.options[seat.selectedIndex];
        if (selected && selected.dataset.flightId !== flight.value) {
            const firstVisible = Array.from(seat.options).find(function (option) {
                return option.dataset.flightId === flight.value;
            });
            if (firstVisible) firstVisible.selected = true;
        }
    }

    flight.addEventListener('change', filterSeats);
    filterSeats();
});
</script>
@endsection
