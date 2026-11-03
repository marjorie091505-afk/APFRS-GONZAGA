<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Display a listing of the flights.
     */
    public function index()
    {
        // Get all flights, newest first
        $flights = Flight::orderBy('departure_time', 'asc')->get();
        
        return view('flights.index', compact('flights'));
    }

    /**
     * Show the form for creating a new flight.
     */
    public function create()
    {
        // Get all airports to populate the dropdowns in the form
        $airports = Airport::all();
        
        return view('flights.create', compact('airports'));
    }

    /**
     * Store a newly created flight in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the form data
        $validated = $request->validate([
            'flight_number' => 'required|string|max:15|unique:flights,flight_number',
          'origin_airport_id' => 'required|string|exists:airports,code',
'destination_airport_id' => 'required|string|exists:airports,code',
            // Note: If your form has an arrival_time input, uncomment the line below:
            // 'arrival_time' => 'required|date|after:departure_time', 
            'base_fare' => 'required|numeric|min:0',
        ]);

        // 2. Apply Business Rule: Uppercase airport codes
        $validated['origin_airport_id'] = strtoupper($validated['origin_airport_id']);
        $validated['destination_airport_id'] = strtoupper($validated['destination_airport_id']);

        // 3. Create the flight in the database
        Flight::create([
    'flight_number' => $validated['flight_number'],
    'origin_airport_id' => $validated['origin_airport_id'],
    'destination_airport_id' => $validated['destination_airport_id'],
    // If the date is missing from the form, this forces it to use the current time
    'departure_time' => $validated['departure_time'] ?? now(), 
    'base_fare' => $validated['base_fare'],
]);

        // 4. Redirect the user back to the flights list with a success message
        return redirect()->route('flights.index')->with('success', 'Flight created successfully!');
    }
}