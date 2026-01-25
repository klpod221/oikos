<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalDataController extends Controller
{
    public function __construct(
        protected ExternalDataService $externalDataService
    ) {}

    /**
     * Get External Data
     *
     * Get weather, exchange rates, and metal prices.
     * Data is cached for 1 hour.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'sometimes|numeric|between:-90,90',
            'lon' => 'sometimes|numeric|between:-180,180',
        ]);

        $lat = $request->input('lat', 21.0285); // Default: Hanoi
        $lon = $request->input('lon', 105.8542);

        $data = $this->externalDataService->getAllData($lat, $lon);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Refresh External Data Cache
     *
     * Force refresh the external data cache.
     */
    public function refresh(): JsonResponse
    {
        $this->externalDataService->invalidateCache();

        return response()->json([
            'success' => true,
            'message' => 'External data cache refreshed',
        ]);
    }
}
