@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <h1 class="text-2xl font-bold mb-6">Create New Flight</h1>

    <!-- Make sure this says route('flights.store') -->
    <form action="{{ route('flights.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Flight Number</label>
            <input type="text" name="flight_number" value="{{ old('flight_number') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            @error('flight_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Origin -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origin Airport</label>
                <select name="origin_airport_id" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                    <option value="">Select Airport</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->code }}" {{ old('origin_airport_id') == $airport->code ? 'selected' : '' }}>
                            {{ $airport->code }} - {{ $airport->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Destination -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destination Airport</label>
                <select name="destination_airport_id" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                    <option value="">Select Airport</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->code }}" {{ old('destination_airport_id') == $airport->code ? 'selected' : '' }}>
                            {{ $airport->code }} - {{ $airport->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departure Time</label>
            <!-- Make sure the name is exactly departure_time -->
            <input type="datetime-local" name="departure_time" value="{{ old('departure_time') }}" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            @error('departure_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Base Fare (₱)</label>
            <input type="number" name="base_fare" value="{{ old('base_fare') }}" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
            @error('base_fare') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Save Flight</button>
            <a href="{{ route('flights.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection