<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Airport;
use App\Services\DomainService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FlightController extends Controller
{
    public function __construct(private DomainService $service) {}

    /**
     * Display a listing of the flights.
     */
    public function index(Request $request): View
    {
        //THIS MUST BE DONE N UR FORM REQUEST
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $flights = Flight::with(['originAirport', 'destinationAirport'])// NEED FURTHER OPTIMIZATION
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

        return view('flights.index', compact('flights', 'search'));
    }

    /**
     * Show the form for creating a new flight.
     */
    public function create()
    {
        // Get all airports to populate the dropdowns in the form
        $airports = Airport::all();// NEED FURTHER OPTIMIZATION: THIS CAN CAUSE N+1

        return view('flights.create', compact('airports'));
    }

    /**
     * Store a newly created flight in storage.
     */
    public function store(Request $request)
    {
        //THIS MUS T BE DONE N UR FORM REQUEST - TRANSFER THIS
        // 1. Validate the form data
        $validated = $request->validate([
            'flight_number' => 'required|string|max:15|unique:flights,flight_number',
            'origin_airport_id' => 'required|string|exists:airports,code',
            'destination_airport_id' => 'required|string|exists:airports,code',
            'departure_time' => 'required|date',
            'base_fare' => 'required|numeric|min:0',
        ]);

        $validated['origin_airport_id'] = strtoupper($validated['origin_airport_id']);
        $validated['destination_airport_id'] = strtoupper($validated['destination_airport_id']);

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
