<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airline Reservation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <nav class="bg-white border-b border-gray-200 px-6 py-4 flex gap-6">
        <a href="{{ route('flights.index') }}" class="font-semibold text-lg hover:text-blue-600">Flights</a>
        <a href="{{ route('bookings.index') }}" class="font-semibold text-lg hover:text-blue-600">Bookings</a>
    </nav>

    <main class="max-w-6xl mx-auto mt-8 px-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>