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
    <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-6">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('flights.index') }}">Flights</a>
        <a href="{{ route('bookings.index') }}">Bookings</a>
        @if(auth()->user()->is_admin)
            <span class="ml-auto rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">Admin</span>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="ml-auto">
            @csrf
            <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-50">
                Log out
            </button>
        </form>
    </nav>

    <main class="max-w-6xl mx-auto mt-8 px-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>