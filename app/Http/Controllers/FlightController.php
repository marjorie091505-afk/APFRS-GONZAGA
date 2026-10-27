<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Models\Airport;
use App\Models\Flight;
use App\Services\DomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FlightController extends Controller
{
    public function __construct(private DomainService $service) {}

    public function index(): View
    {
        $flights = Flight::with(['originAirport', 'destinationAirport'])
            ->withCount(['availableSeats as available_seats_count'])
            ->latest('departure_time')
            ->paginate(10);

        return view('flights.index', compact('flights'));
    }

    public function create(): View
    {
        $airports = Airport::orderBy('code')->get();
        return view('flights.create', compact('airports'));
    }

    public function store(StoreFlightRequest $request): RedirectResponse
    {
        $this->service->createFlight($request->validated());

        return redirect()->route('flights.index')
            ->with('success', 'Flight created successfully and seats generated.');
    }

    public function update(UpdateFlightRequest $request, $id): RedirectResponse
    {
        $flight = Flight::findOrFail($id);
        $this->service->updateFlight($flight, $request->validated());

        return redirect()->route('flights.index')
            ->with('success', 'Flight updated successfully.');
    }
}