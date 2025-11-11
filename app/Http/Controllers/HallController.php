<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;

class HallController extends Controller
{
    /**
     * Get all active halls for user selection
     */
    public function index(Request $request)
    {
        try {
            $gender = $request->input('gender');
            $search = $request->input('search');

            $query = Hall::where('is_active', true);

            // Filter by gender
            if ($gender && in_array($gender, ['male', 'female', 'mixed'])) {
                $query->where('gender', $gender);
            }

            // Search by name
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $halls = $query->orderBy('name', 'asc')->get();

            return ApiResponse::success([
                'halls' => $halls
            ], 'Halls retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve halls: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve halls', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


}

