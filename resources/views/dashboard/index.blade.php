@extends('layouts.app')

@section('content')
@php
    $dashboardCards = [
        ['label' => 'Total Flights', 'value' => number_format($stats['flights']), 'href' => route('flights.index'), 'valueClass' => 'text-gray-900'],
        ['label' => 'Total Bookings', 'value' => number_format($stats['bookings']), 'href' => route('bookings.index'), 'valueClass' => 'text-gray-900'],
        ['label' => 'Passengers', 'value' => number_format($stats['passengers']), 'href' => route('bookings.index'), 'valueClass' => 'text-gray-900'],
        ['label' => 'Available Seats', 'value' => number_format($stats['available_seats']), 'href' => route('flights.index'), 'valueClass' => 'text-gray-900'],
        ['label' => 'Upcoming Flights', 'value' => number_format($stats['upcoming_flights']), 'href' => route('flights.index'), 'valueClass' => 'text-gray-900'],
        ['label' => 'Total Revenue', 'value' => '₱'.number_format($stats['revenue'], 2), 'href' => route('bookings.index'), 'valueClass' => 'text-green-600'],
    ];
@endphp
<div class="space-y-8">
    <div>
        <p class="text-sm font-semibold text-blue-600">Airline Operations</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-gray-500">Overview of flights, bookings, passengers, and revenue.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($dashboardCards as $card)
            @if(auth()->user()->is_admin)
                <a href="{{ $card['href'] }}"
                   aria-label="{{ $card['label'] }}: {{ $card['value'] }}"
                   class="block rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600">
            @else
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            @endif
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-bold {{ $card['valueClass'] }}">{{ $card['value'] }}</p>
            @if(auth()->user()->is_admin)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="text-lg font-bold">Recent Bookings</h2>
                <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-sm text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Reference</th>
                            <th class="px-6 py-3">Passenger</th>
                            <th class="px-6 py-3">Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td class="px-6 py-4 font-mono text-sm">{{ $booking->booking_reference }}</td>
                                <td class="px-6 py-4">{{ $booking->passenger->full_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 font-semibold text-green-600">₱{{ number_format($booking->total_paid, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">No bookings yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="text-lg font-bold">Upcoming Flights</h2>
                <a href="{{ route('flights.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($upcomingFlights as $flight)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-bold">{{ $flight->flight_number }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $flight->originAirport->code ?? 'N/A' }}
                                →
                                {{ $flight->destinationAirport->code ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">{{ $flight->departure_time->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $flight->departure_time->format('h:i A') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-8 text-center text-gray-500">No upcoming flights.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
