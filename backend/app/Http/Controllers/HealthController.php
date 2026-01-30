<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Check system health and server time.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check DB connection
        try {
            DB::connection()->getPdo();
            $dbStatus = 'ok';
        } catch (\Exception $e) {
            $dbStatus = 'error: ' . $e->getMessage();
        }

        return response()->json([
            'status' => 'ok',
            'server_time' => Carbon::now()->toIso8601String(),
            'server_timezone' => config('app.timezone'),
            'database_status' => $dbStatus,
            'timestamp' => Carbon::now()->timestamp,
        ]);
    }
}
