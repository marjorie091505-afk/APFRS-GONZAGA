@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm mt-10">
    
    <!-- Header and Button -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Bookings</h1>
        
        <!-- THIS IS THE FIX: It now uses route('bookings.create') -->
        <a href="{{ route('bookings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            + New Booking
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-md mb-4 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Bookings Table -->
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="p-3">Booking Ref</th>
                <th class="p-3">Passport</th>
                <th class="p-3">Flight</th>
                <th class="p-3">Seat</th>
                <th class="p-3">Total Paid</th>
                @if(auth()->user()->is_admin)
                    <th class="p-3 text-right">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
    @forelse($bookings as $booking)
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3 font-mono text-sm">{{ $booking->booking_reference }}</td>
            
            <!-- Get passport from the passenger relationship -->
            <td class="p-3">{{ $booking->passenger->passport_number ?? 'N/A' }}</td>
            
            <!-- Get flight number from the flight relationship -->
            <td class="p-3">{{ $booking->flight->flight_number ?? 'N/A' }}</td>
            
            <td class="p-3">{{ $booking->seat_class }}</td>
            <td class="p-3 font-bold text-green-600">₱{{ number_format($booking->total_paid, 2) }}</td>
            @if(auth()->user()->is_admin)
                <td class="p-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('bookings.edit', $booking) }}"
                           class="rounded-md border border-blue-300 px-3 py-1.5 text-sm font-medium text-blue-600 hover:bg-blue-50"
                           aria-label="Edit booking">
                            Edit
                        </a>
                        <form method="POST"
                              action="{{ route('bookings.destroy', $booking) }}"
                              onsubmit="return confirm('Delete this booking?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-300 text-red-600 hover:bg-red-50"
                                    aria-label="Delete booking"
                                    title="Delete booking">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="h-4 w-4" aria-hidden="true">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="M19 6l-1 14H6L5 6"></path>
                                    <path d="M10 11v5"></path>
                                    <path d="M14 11v5"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="{{ auth()->user()->is_admin ? 6 : 5 }}" class="p-3 text-center text-gray-500">No bookings found yet.</td>
        </tr>
    @endforelse
</tbody>
    </table>
</div>
@endsection