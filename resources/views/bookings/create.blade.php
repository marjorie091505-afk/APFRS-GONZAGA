@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <h1 class="text-2xl font-bold mb-6">Book a Flight</h1>

    <form action="{{ route('bookings.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Passenger Passport</label>
            <input type="text" name="passenger_passport" value="{{ old('passenger_passport') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            @error('passenger_passport') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Passenger Name</label>
            <input type="text" name="passenger_name" value="{{ old('passenger_name') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            @error('passenger_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Flight</label>
            <select name="flight_number" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                <option value="">Select a Flight</option>
                @foreach($flights as $flight)
                    <option value="{{ $flight->flight_number }}" {{ old('flight_number') == $flight->flight_number ? 'selected' : '' }}>
                        {{ $flight->flight_number }} ({{ $flight->origin_airport_id }} to {{ $flight->destination_airport_id }})
                    </option>
                @endforeach
            </select>
            @error('flight_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seat Number</label>
                <input type="text" name="seat_number" value="{{ old('seat_number') }}" placeholder="e.g. 12A" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                @error('seat_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seat Class</label>
                <select name="seat_class" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                    <option value="Economy">Economy</option>
                    <option value="Business">Business</option>
                    <option value="First">First</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Baggage Fee (₱)</label>
            <input type="number" name="baggage_fee" value="{{ old('baggage_fee', 0) }}" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            @error('baggage_fee') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Confirm Booking</button>
            <a href="{{ route('bookings.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection