@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-sm font-semibold text-blue-600">Airline Reservations</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-gray-500">View upcoming flights and manage reservations.</p>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h2 class="text-lg font-bold">Upcoming Flights</h2>
                <p class="text-sm text-gray-500">Your reserved flight schedule</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($upcomingFlights as $booking)
                @php
                    $flight = $booking->flight;
                    $departure = $flight->departure_time;
                    $departingNow = $departure->between(now()->subMinutes(30), now()->addMinutes(30));
                @endphp
                <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold">{{ $flight->flight_number }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $flight->originAirport->code ?? 'N/A' }}
                            →
                            {{ $flight->destinationAirport->code ?? 'N/A' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Seat {{ $booking->seat->seat_number ?? 'N/A' }} · Ref {{ $booking->booking_reference }}
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm font-semibold">{{ $departure->format('M d, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ $departure->format('h:i A') }}</p>
                        <span class="mt-1 inline-block rounded-full px-2 py-1 text-xs font-semibold {{ $departingNow ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">
                            {{ $departingNow ? 'Departing now' : 'Upcoming' }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-gray-500">You have no upcoming reserved flights.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
