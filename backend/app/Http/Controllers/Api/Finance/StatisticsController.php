<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Statistics Controller
 *
 * Handles retrieval of financial statistics.
 *
 * @package App\Http\Controllers\Api\Finance
 */
class StatisticsController extends Controller
{
    public function __construct(
        protected StatisticsService $statisticsService
    ) {
    }

    /**
     * Get Statistics
     *
     * Get comprehensive financial statistics for a specific time period.
     * Supports preset periods (this_month, last_month, this_year, last_year) and custom date ranges.
     * Results are cached for performance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'sometimes|in:this_month,last_month,this_year,last_year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $period = $request->input('period', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $statistics = $this->statisticsService->getStatistics(
            $request->user()->id,
            $period,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Refresh Cache
     *
     * Force refresh statistics cache for the authenticated user.
     */
    public function refresh(Request $request): JsonResponse
    {
        $this->statisticsService->invalidateCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Statistics cache refreshed successfully',
        ]);
    }
}
