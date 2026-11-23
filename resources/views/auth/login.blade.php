<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Gonzaga Air</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="grid min-h-screen lg:grid-cols-[1.08fr_0.92fr]">
        <section class="relative hidden flex-col justify-between overflow-hidden bg-[#071a3d] p-12 text-white lg:flex xl:p-16">
            <div class="absolute -right-24 -top-20 h-96 w-96 rounded-full border border-blue-300/30" aria-hidden="true"></div>
            <div class="absolute -bottom-48 -left-40 h-[32rem] w-[32rem] rounded-full border border-blue-300/20" aria-hidden="true"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-2xl">✈</div>
                <div>
                    <p class="text-lg font-semibold">Gonzaga Air</p>
                    <p class="text-xs text-blue-200">Passenger Reservation</p>
                </div>
            </div>
            <div class="relative max-w-xl">
                <p class="mb-5 text-sm font-semibold uppercase tracking-[0.25em] text-blue-300">Welcome aboard</p>
                <h1 class="text-5xl font-semibold leading-tight tracking-tight xl:text-6xl">Your journey starts here.</h1>
                <p class="mt-6 max-w-md leading-7 text-blue-100/80">Sign in to manage flights, reservations, and passenger details in one place.</p>
            </div>
            <p class="relative text-xs text-blue-200/70">Safe travels. Smooth journeys.</p>
        </section>

        <section class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#071a3d] text-2xl text-white">✈</div>
                    <div>
                        <p class="font-semibold text-[#071a3d]">Gonzaga Air</p>
                        <p class="text-xs text-slate-500">Passenger Reservation</p>
                    </div>
                </div>

                <div class="mb-8">
                    <p class="mb-2 text-sm font-semibold text-blue-700">Welcome back</p>
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">Sign in to your account</h2>
                    <p class="mt-3 text-sm text-slate-500">Enter your details to continue.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@example.com" class="h-12 w-full rounded-lg border border-slate-200 bg-white px-4 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                            @if ($canResetPassword)
                                <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">Forgot password?</a>
                            @endif
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="h-12 w-full rounded-lg border border-slate-200 bg-white px-4 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @error('password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label for="remember" class="flex items-center gap-3 text-sm text-slate-600">
                        <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                        Remember me
                    </label>

                    <button type="submit" class="h-12 w-full rounded-lg bg-[#0757c9] px-4 font-semibold text-white transition hover:bg-[#064cae]">
                        Sign in
                    </button>
                </form>

                <p class="mt-7 text-center text-sm text-slate-500">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900">Create account</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
