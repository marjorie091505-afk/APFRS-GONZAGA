<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airline Reservation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        (() => {
            const applyAppearance = (appearance) => {
                document.documentElement.classList.toggle('dark', appearance === 'dark');
                document.querySelectorAll('[data-appearance-choice]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(button.dataset.appearanceChoice === appearance));
                });
            };

            const savedAppearance = localStorage.getItem('appearance') || 'light';
            applyAppearance(savedAppearance);

            window.setAirlineAppearance = (appearance) => {
                if (!['light', 'dark'].includes(appearance)) {
                    return;
                }

                localStorage.setItem('appearance', appearance);
                applyAppearance(appearance);
            };

            document.addEventListener('DOMContentLoaded', () => applyAppearance(savedAppearance));
        })();
    </script>
    <style>
        html.dark body {
            background-color: #111827;
            color: #f9fafb;
        }

        html.dark .bg-white {
            background-color: #1f2937 !important;
        }

        html.dark .bg-gray-50 {
            background-color: #1f2937 !important;
        }

        html.dark .bg-gray-100 {
            background-color: #374151 !important;
        }

        html.dark .text-gray-900,
        html.dark .text-gray-800,
        html.dark .text-gray-700 {
            color: #f3f4f6 !important;
        }

        html.dark .text-gray-600,
        html.dark .text-gray-500 {
            color: #9ca3af !important;
        }

        html.dark .border-gray-100,
        html.dark .border-gray-200,
        html.dark .border-gray-300 {
            border-color: #374151 !important;
        }

        html.dark input:not([type="checkbox"]) {
            background-color: #111827;
            border-color: #4b5563;
            color: #f9fafb;
        }

        html.dark input::placeholder {
            color: #9ca3af;
        }

        html.dark .hover\:bg-gray-50:hover {
            background-color: #374151 !important;
        }

        [data-appearance-choice][aria-pressed="true"] {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        html.dark [data-appearance-choice][aria-pressed="true"] {
            border-color: #60a5fa;
            background-color: #1e3a8a;
            color: #eff6ff;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <div class="min-h-screen md:flex">
        <aside class="flex w-full flex-col border-b border-gray-200 bg-white md:min-h-screen md:w-64 md:border-r md:border-b-0">
            <a href="{{ route('dashboard') }}" class="px-6 py-5 text-lg font-bold text-gray-900">Airline Reservations</a>
            <nav aria-label="Main navigation" class="grid grid-cols-2 gap-2 px-4 md:flex md:flex-col">
                @php
                    $navigationItems = [
                        ['label' => 'Dashboard', 'route' => 'dashboard'],
                        ['label' => 'Flights', 'route' => 'flights.index'],
                        ['label' => 'Bookings', 'route' => 'bookings.index'],
                        ['label' => 'Settings', 'route' => 'profile.edit'],
                    ];
                @endphp
                @foreach($navigationItems as $item)
                    @php
                        $isActive = $item['route'] === 'profile.edit'
                            ? request()->routeIs('profile.*', 'password.*', 'appearance')
                            : request()->routeIs($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       @if($isActive) aria-current="page" @endif
                       class="rounded-md px-3 py-2 text-sm font-medium {{ $isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 px-6 py-4 md:mt-auto">
                <div>
                    <p class="font-semibold">{{ auth()->user()->name }}</p>
                    @if(auth()->user()->is_admin)
                        <span class="text-xs font-semibold text-blue-700">Admin</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium hover:bg-gray-50">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <main class="mx-auto mt-8 max-w-6xl px-4">
                @if(session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-100 p-4 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-100 p-4 text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>