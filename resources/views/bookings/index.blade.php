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
        </tr>
    @empty
        <tr>
            <td colspan="5" class="p-3 text-center text-gray-500">No bookings found yet.</td>
        </tr>
    @endforelse
</tbody>
    </table>
</div>
@endsection