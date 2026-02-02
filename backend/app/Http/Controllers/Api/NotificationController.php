<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessNotificationJob;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Store a new notification log.
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("Notification API hit", $request->all());

        $request->validate([
            'package_name' => 'required|string',
            'content' => 'required|string',
            'title' => 'nullable|string',
            'timestamp' => 'nullable|numeric',
        ]);

        $user = Auth::user();

        // Generate Dedup Hash
        $content = $request->input('content');
        $timestamp = $request->input('timestamp') ?? time();
        // We use content + rough timestamp window (to allow slight variations) or just content + date if we want strictness.
        // Let's use content + package + user + (timestamp / 60) to allow same message in different minutes but dedup bursts.
        $dedupKey = $content . $request->input('package_name') . $user->id . floor($timestamp / 60);
        $hash = md5($dedupKey);

        // Check Duplicate
        if (NotificationLog::where('hash', $hash)->exists()) {
            return response()->json(['message' => 'Duplicate ignored'], 200);
        }

        // Create Log
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'package_name' => $request->input('package_name'),
            'title' => $request->input('title'),
            'content' => $content,
            'hash' => $hash,
            'status' => 'pending',
        ]);

        // Dispatch Job
        ProcessNotificationJob::dispatch($log);

        return response()->json([
            'message' => 'Notification queued',
            'id' => $log->id
        ], 201);
    }
}
