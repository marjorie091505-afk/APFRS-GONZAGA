@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <h1 class="text-2xl font-bold mb-6">Create New Flight</h1>

    <form action="{{ route('flights.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Flight Number</label>
            <input type="text" name="flight_number" value="{{ old('flight_number') }}" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            @error('flight_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origin Airport</label>
                <select name="origin_airport_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <option value="">Select Airport</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->id }}" {{ old('origin_airport_id') == $airport->id ? 'selected' : '' }}>
                            {{ $airport->code }} - {{ $airport->name }}
                        </option>
                    @endforeach
                </select>
                @error('origin_airport_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destination Airport</label>
                <select name="destination_airport_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <option value="">Select Airport</option>
                    @foreach($airports as $airport)
                        <option value="{{ $airport->id }}" {{ old('destination_airport_id') == $airport->id ? 'selected' : '' }}>
                            {{ $airport->code }} - {{ $airport->name }}
                        </option>
                    @endforeach
                </select>
                @error('destination_airport_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departure Time</label>
                <input type="datetime-local" name="departure_time" value="{{ old('departure_time') }}" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                @error('departure_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Arrival Time</label>
                <input type="datetime-local" name="arrival_time" value="{{ old('arrival_time') }}" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                @error('arrival_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Base Fare (₱)</label>
            <input type="number" step="0.01" name="base_fare" value="{{ old('base_fare') }}" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            @error('base_fare') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-4 pt-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                Save Flight
            </button>
            <a href="{{ route('flights.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</div>
@endsection