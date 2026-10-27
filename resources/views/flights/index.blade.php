@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Flights</h1>
    <a href="{{ route('flights.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
        + Add Flight
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-medium">
            <tr>
                <th class="px-6 py-3">Flight No.</th>
                <th class="px-6 py-3">Route</th>
                <th class="px-6 py-3">Departure</th>
                <th class="px-6 py-3">Base Fare</th>
                <th class="px-6 py-3">Available Seats</th>
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
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $flight->available_seats_count }} seats
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No flights found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $flights->links() }}</div>
@endsection