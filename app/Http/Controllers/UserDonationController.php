<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;

class UserDonationController extends Controller
{
    /**
     * Get all featured donations for alumni dashboard
     */
    public function getFeaturedDonations(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $featuredDonations = Donation::where('is_featured', true)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success($featuredDonations, 'Featured donations retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve featured donations: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve featured donations', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all active donations (featured and non-featured)
     */
    public function getAllDonations(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $category = $request->input('category');
            $search = $request->input('search');

            $query = Donation::where('is_active', true)
                ->whereNull('deleted_at')
                ->where(function($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                });

            // Filter by category
            if ($category) {
                $query->where('category', $category);
            }

            // Search by title or description
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $donations = $query->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success($donations, 'Donations retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve donations: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve donations', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get a single donation by ID or UUID
     */
    public function getDonation($id)
    {
        try {
            // Try to find by UUID first, then by ID
            $donation = Donation::where('donation_uuid', $id)
                ->orWhere('id', $id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->firstOrFail();

            return ApiResponse::success([
                'donation' => $donation
            ], 'Donation retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Donation not found', 404);
        }
    }

    /**
     * Get donation categories
     */
    public function getCategories()
    {
        try {
            $categories = [
                'infrastructure' => 'Infrastructure',
                'scholarships' => 'Scholarships',
                'research' => 'Research',
                'sports' => 'Sports',
                'library' => 'Library',
                'technology' => 'Technology',
                'healthcare' => 'Healthcare',
                'general' => 'General Fund',
                'other' => 'Other'
            ];

            return ApiResponse::success([
                'categories' => $categories
            ], 'Categories retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve categories', 500);
        }
    }

    /**
     * Get donation statistics for user dashboard
     */
    public function getDonationStatistics()
    {
        try {
            $totalActiveDonations = Donation::where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            $featuredDonations = Donation::where('is_featured', true)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->count();

            $totalTargetAmount = Donation::where('is_active', true)
                ->whereNull('deleted_at')
                ->sum('target_amount');

            $totalRaisedAmount = Donation::where('is_active', true)
                ->whereNull('deleted_at')
                ->sum('current_amount');

            return ApiResponse::success([
                'total_active_donations' => $totalActiveDonations,
                'featured_donations' => $featuredDonations,
                'total_target_amount' => $totalTargetAmount,
                'total_raised_amount' => $totalRaisedAmount,
                'completion_percentage' => $totalTargetAmount > 0
                    ? round(($totalRaisedAmount / $totalTargetAmount) * 100, 2)
                    : 0
            ], 'Statistics retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve donation statistics: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve statistics', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

