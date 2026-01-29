<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * External Data Controller
 *
 * Handles requests for weather, exchange rates, and metal prices.
 *
 * @package App\Http\Controllers\Api
 */
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'sometimes|numeric|between:-90,90',
            'lon' => 'sometimes|numeric|between:-180,180',
        ]);

        $lat = $request->input('lat');
        $lon = $request->input('lon');

        if (!$lat || !$lon) {
            $user = $request->user();
            if ($user && $user->settings) {
                $lat = $lat ?? $user->settings->latitude;
                $lon = $lon ?? $user->settings->longitude;
            }
        }

        // Default: Hanoi
        $lat = $lat ?? 21.0285;
        $lon = $lon ?? 105.8542;

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
