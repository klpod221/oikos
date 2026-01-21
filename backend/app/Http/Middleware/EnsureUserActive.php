<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure user account is active (not blocked or pending)
 */
class EnsureUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isBlocked()) {
            // Revoke all tokens when blocked
            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked. Please contact support.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please verify your email or contact support.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
