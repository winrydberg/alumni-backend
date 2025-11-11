<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Responses\ApiResponse;

class CheckAdminScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Check if the token has 'admin' scope
        if (!$request->user()->tokenCan('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // Verify the user is actually an Admin model instance
        if (!($request->user() instanceof \App\Models\Admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin account required.',
            ], 403);
        }

        return $next($request);
    }
}
