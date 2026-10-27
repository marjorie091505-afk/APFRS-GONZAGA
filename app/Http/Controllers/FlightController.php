<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Models\Airport;
use App\Services\DomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    public function __construct(private DomainService $service) {}

    /** GET /flights — paginated with eager-loaded relations */
    public function index(): JsonResponse
    {
        return response()->json($this->service->paginateFlights());
    }

    /** GET /flights/create — dependency data */
    public function create(): JsonResponse
    {
        return response()->json([
            'airports' => Airport::orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    /** POST /flights */
    public function store(StoreFlightRequest $request): JsonResponse
    {
        $flight = $this->service->createFlight($request->validated());

        return response()->json([
            'message' => 'Flight created successfully.',
            'data'    => $flight,
        ], 201);
    }

    /** PUT/PATCH /flights/{flight} */
    public function update(UpdateFlightRequest $request, int $id): JsonResponse
    {
        $flight = $this->service->updateFlight($id, $request->validated());

        return response()->json([
            'message' => 'Flight updated successfully.',
            'data'    => $flight,
        ]);
    }
}