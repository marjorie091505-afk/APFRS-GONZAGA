@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <h1 class="text-2xl font-bold mb-6">Create New Reservation</h1>

    <form action="{{ route('bookings.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Flight Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Flight</label>
            <select name="flight_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                <option value="">Choose a flight</option>
                @foreach($flights as $flight)
                    <option value="{{ $flight->id }}">
                        {{ $flight->flight_number }} ({{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Seat Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Available Seat</label>
            <select name="passenger_seat_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                <option value="">Choose a seat</option>
                @foreach($flights as $flight)
                    <optgroup label="{{ $flight->flight_number }}">
                        @foreach($flight->availableSeats as $seat)
                            <option value="{{ $seat->id }}">
                                Seat {{ $seat->seat_number }} ({{ ucfirst($seat->seat_class) }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <!-- Passenger Info -->
        <div class="border-t border-gray-100 pt-4 mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Passport Number</label>
                    <input type="text" name="passport_number" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Baggage Fee</label>
            <input type="number" name="baggage_fee" value="0" class="w-full border border-gray-300 rounded-md px-3 py-2">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Confirm Reservation</button>
    </form>
</div>
@endsection
