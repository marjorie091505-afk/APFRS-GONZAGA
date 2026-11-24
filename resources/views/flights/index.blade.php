@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Flights</h1>
    <a href="{{ route('flights.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
        + Add Flight
    </a>
</div>

<form method="GET" action="{{ route('flights.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row">
    <label for="flight-search" class="sr-only">Search flights</label>
    <input
        id="flight-search"
        name="search"
        type="search"
        value="{{ $search }}"
        placeholder="Search flight number, airport, or city"
        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 sm:max-w-md"
    >
    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
        Search
    </button>
    @if($search !== '')
        <a href="{{ route('flights.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-center hover:bg-gray-50">
            Clear
        </a>
    @endif
</form>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-medium">
            <tr>
                <th class="px-6 py-3">Flight No.</th>
                <th class="px-6 py-3">Route</th>
                <th class="px-6 py-3">Departure</th>
                <th class="px-6 py-3">Base Fare</th>
                <th class="px-6 py-3">Available Seats</th>
                @if(auth()->user()->is_admin)
                    <th class="px-6 py-3 text-right">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($flights as $flight)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold">{{ $flight->flight_number }}</td>
                <td class="px-6 py-4">
                    {{ $flight->originAirport->code }} → {{ $flight->destinationAirport->code }}
                </td>
                <td class="px-6 py-4">{{ $flight->departure_time->format('M d, Y h:i A') }}</td>
                <td class="px-6 py-4">₱{{ number_format($flight->base_fare, 2) }}</td>
                <td class="px-6 py-4">
                    {{ $flight->available_seats_count }}
                </td>
                @if(auth()->user()->is_admin)
                    <td class="px-6 py-4 text-right">
                        <form method="POST"
                              action="{{ route('flights.destroy', $flight) }}"
                              onsubmit="return confirm('Are you sure you want to delete this flight?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->is_admin ? 6 : 5 }}" class="px-6 py-8 text-center text-gray-500">
                    {{ $search !== '' ? 'No flights match your search.' : 'No flights found.' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $flights->links() }}</div>
@endsection