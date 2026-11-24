<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Airport;
use App\Http\Requests\IndexFlightRequest;
use App\Http\Requests\StoreFlightRequest;
use App\Services\DomainService;
use Database\Seeders\AirportSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FlightController extends Controller
{
    public function __construct(private DomainService $service) {}

    /**
     * Display a listing of the flights.
     */
    public function index(IndexFlightRequest $request): Response
    {
        $validated = $request->validated();
        $search = trim($validated['search'] ?? '');

        $flights = Flight::with(['originAirport', 'destinationAirport'])
            ->withCount('availableSeats')
            ->when($search !== '', function (Builder $query) use ($search) {
                $term = '%'.$search.'%';

                $query->where(function (Builder $query) use ($term) {
                    $query->where('flight_number', 'like', $term)
                        ->orWhereHas('originAirport', function (Builder $airportQuery) use ($term) {
                            $airportQuery->where('code', 'like', $term)
                                ->orWhere('name', 'like', $term)
                                ->orWhere('city', 'like', $term);
                        })
                        ->orWhereHas('destinationAirport', function (Builder $airportQuery) use ($term) {
                            $airportQuery->where('code', 'like', $term)
                                ->orWhere('name', 'like', $term)
                                ->orWhere('city', 'like', $term);
                        });
                });
            })
            ->orderBy('departure_time')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('flights/index', compact('flights', 'search'));
    }

    /**
     * Show the form for creating a new flight.
     */
    public function create()
    {
        abort_unless(auth()->user()->is_admin, 403);

        if (Airport::count() === 0) {
            app(AirportSeeder::class)->run();
        }

        $airports = Airport::orderBy('code')->get();

        return Inertia::render('flights/create', compact('airports'));
    }

    /**
     * Store a newly created flight in storage.
     */
    public function store(StoreFlightRequest $request)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $flight = Flight::create([
                'flight_number' => $validated['flight_number'],
                'origin_airport_id' => $validated['origin_airport_id'],
                'destination_airport_id' => $validated['destination_airport_id'],
                'departure_time' => $validated['departure_time'],
                'base_fare' => $validated['base_fare'],
            ]);

            $this->service->ensureSeatsForFlight($flight);
        });

        return redirect()->route('flights.index')->with('success', 'Flight created successfully!');
    }

    public function destroy(Flight $flight): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        if ($flight->bookings()->exists()) {
            return redirect()->route('flights.index')
                ->with('error', 'This flight cannot be deleted because it has existing bookings.');
        }

        $flight->delete();

        return redirect()->route('flights.index')
            ->with('success', 'Flight deleted successfully!');
    }
}
