<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\FlightController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('flights.index');
});

Route::get('/flights', [FlightController::class, 'index'])->name('flights.index');
Route::get('/flights/create', [FlightController::class, 'create'])->name('flights.create');
Route::post('/flights', [FlightController::class, 'store'])->name('flights.store');

Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');