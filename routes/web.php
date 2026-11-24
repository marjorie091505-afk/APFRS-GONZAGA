<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 1. Send signed-in users to the dashboard, and guests to the login page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// 2. Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. Protect your system (Only logged-in users can see these)
Route::middleware('auth')->group(function () {
    Route::get('/flights', [FlightController::class, 'index'])->name('flights.index');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

    Route::middleware('admin')->group(function () {
        Route::get('/flights/create', [FlightController::class, 'create'])->name('flights.create');
        Route::post('/flights', [FlightController::class, 'store'])->name('flights.store');
        Route::delete('/flights/{flight}', [FlightController::class, 'destroy'])->name('flights.destroy');

        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    });
});

// 4. This loads the login and register forms
require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
