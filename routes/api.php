<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('flights', FlightController::class)->only(['index', 'create', 'store', 'update']);
Route::apiResource('bookings', BookingController::class)->only(['index', 'create', 'store', 'update']);