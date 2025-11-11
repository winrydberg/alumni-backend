<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donation;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    /**
     * Get all donations
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status'); // active, inactive, all
            $category = $request->input('category');

            $query = Donation::with('creator');

            // Filter by status
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            // Filter by category
            if ($category) {
                $query->where('category', $category);
            }

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            $donations = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return ApiResponse::success($donations, 'Donations retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve donations', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get a single donation
     */
    public function show($id)
    {
        try {
            $donation = Donation::with('creator')->findOrFail($id);

            return ApiResponse::success([
                'donation' => $donation,
                'is_currently_active' => $donation->isCurrentlyActive()
            ], 'Donation retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Donation not found', 404);
        }
    }

    /**
     * Create a new donation
     */
    public function store(Request $request)
    {
        // Parse payment_methods if it's a JSON string
        $data = $request->all();
        if (isset($data['payment_methods']) && is_string($data['payment_methods'])) {
            $data['payment_methods'] = json_decode($data['payment_methods'], true);
        }

        $validator = \Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'nullable|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|in:true,false,1,0',
            'is_featured' => 'nullable|in:true,false,1,0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
            'payment_methods' => 'nullable|array',
            'terms_and_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            return ApiResponse::error($firstError, 422, $errors->messages());
        }

        try {
            $imageUrl = null;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('donations', $imageName, 'public');
                $imageUrl = Storage::url($imagePath);
            }

            // Convert string booleans to actual booleans
            $isActive = $request->is_active === 'true' || $request->is_active === '1' || $request->is_active === 1 || $request->is_active === true;
            $isFeatured = $request->is_featured === 'true' || $request->is_featured === '1' || $request->is_featured === 1 || $request->is_featured === true;

            $donation = Donation::create([
                'donation_uuid' => Str::uuid7(),
                'title' => $request->title,
                'description' => $request->description,
                'target_amount' => $request->target_amount,
                'minimum_amount' => $request->minimum_amount ?? 0,
                'category' => $request->category,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $isActive,
                'is_featured' => $isFeatured,
                'image_url' => $imageUrl,
                'payment_methods' => $data['payment_methods'] ?? null,
                'terms_and_conditions' => $request->terms_and_conditions,
                'created_by' => $request->user()->id,
            ]);

            return ApiResponse::success([
                'donation' => $donation
            ], 'Donation created successfully', 201);
        } catch (\Exception $e) {
            Log::error($e);
            return ApiResponse::error('Failed to create donation', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update a donation
     */
    public function update(Request $request, $id)
    {
        // Parse payment_methods if it's a JSON string
        $data = $request->all();
        if (isset($data['payment_methods']) && is_string($data['payment_methods'])) {
            $data['payment_methods'] = json_decode($data['payment_methods'], true);
        }

        $validator = \Validator::make($data, [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'nullable|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|in:true,false,1,0,"1","0","true","false"',
            'is_featured' => 'nullable|in:true,false,1,0,"1","0","true","false"',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
            'payment_methods' => 'nullable|array',
            'terms_and_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            return ApiResponse::error($firstError, 422, $errors->messages());
        }

        try {
            $donation = Donation::findOrFail($id);

            // Log incoming request data for debugging
            Log::info('Update request data:', $data);

            // Prepare update data - only include fields that are actually provided
            $updateData = [];

            if ($request->has('title')) $updateData['title'] = $request->title;
            if ($request->has('description')) $updateData['description'] = $request->description;
            if ($request->has('target_amount')) $updateData['target_amount'] = $request->target_amount;
            if ($request->has('minimum_amount')) $updateData['minimum_amount'] = $request->minimum_amount;
            if ($request->has('category')) $updateData['category'] = $request->category;
            if ($request->has('start_date')) $updateData['start_date'] = $request->start_date;
            if ($request->has('end_date')) $updateData['end_date'] = $request->end_date;
            if (isset($data['payment_methods'])) $updateData['payment_methods'] = $data['payment_methods'];
            if ($request->has('terms_and_conditions')) $updateData['terms_and_conditions'] = $request->terms_and_conditions;

            // Handle is_active
            if ($request->has('is_active')) {
                $isActiveValue = $request->is_active;
                // Convert string booleans to actual booleans
                // Accept: "1", "true", 1, true as true
                // Accept: "0", "false", 0, false as false
                $updateData['is_active'] = filter_var($isActiveValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($isActiveValue === '1' || $isActiveValue === 1);
            }

            // Handle is_featured
            if ($request->has('is_featured')) {
                $isFeaturedValue = $request->is_featured;
                // Convert string booleans to actual booleans
                $updateData['is_featured'] = filter_var($isFeaturedValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($isFeaturedValue === '1' || $isFeaturedValue === 1);
            }

            // Handle image upload if a new image is provided
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($donation->image_url) {
                    $oldImagePath = str_replace('/storage/', '', $donation->image_url);
                    Storage::disk('public')->delete($oldImagePath);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('donations', $imageName, 'public');
                $updateData['image_url'] = Storage::url($imagePath);
            }

            // Log what will be updated
            Log::info('Update data to be applied:', $updateData);

            // Check if there's anything to update
            if (empty($updateData)) {
                Log::warning('No update data provided - updateData array is empty');
                return ApiResponse::error('No data provided to update', 400);
            }

            // Update the donation
            $donation->update($updateData);

            Log::info('Donation updated successfully', ['id' => $id, 'updated_fields' => array_keys($updateData)]);

            return ApiResponse::success([
                'donation' => $donation->fresh()
            ], 'Donation updated successfully');
        } catch (\Exception $e) {
            Log::error('Donation update error: ' . $e->getMessage());
            return ApiResponse::error('Failed to update donation', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete (soft delete) a donation
     */
    public function destroy($id)
    {
        try {
            $donation = Donation::findOrFail($id);
            $donation->delete();

            return ApiResponse::success([], 'Donation deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete donation', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Restore a soft-deleted donation
     */
    public function restore($id)
    {
        try {
            $donation = Donation::withTrashed()->findOrFail($id);
            $donation->restore();

            return ApiResponse::success([
                'donation' => $donation
            ], 'Donation restored successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to restore donation', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle donation active status
     */
    public function toggleStatus($id)
    {
        try {
            $donation = Donation::findOrFail($id);
            $donation->is_active = !$donation->is_active;
            $donation->save();

            return ApiResponse::success([
                'donation' => $donation,
                'status' => $donation->is_active ? 'active' : 'inactive'
            ], 'Donation status toggled successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to toggle donation status', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle donation featured status
     */
    public function toggleFeatured($id)
    {
        try {
            $donation = Donation::findOrFail($id);
            $donation->is_featured = !$donation->is_featured;
            $donation->save();

            return ApiResponse::success([
                'donation' => $donation,
                'is_featured' => $donation->is_featured
            ], 'Donation featured status toggled successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to toggle featured status', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get donation categories
     */
    public function getCategories()
    {
        try {
            $categories = Donation::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category');

            return ApiResponse::success([
                'categories' => $categories
            ], 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve categories', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get donation statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_donations' => Donation::count(),
                'active_donations' => Donation::active()->count(),
                'featured_donations' => Donation::featured()->count(),
                'inactive_donations' => Donation::where('is_active', false)->count(),
                'total_target_amount' => Donation::active()->sum('target_amount'),
            ];

            return ApiResponse::success($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve statistics', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all contributions/payments for a specific donation
     */
    public function getContributions(Request $request, $donation_uuid)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $status = $request->input('status'); // completed, pending, failed, all

            // Find donation by UUID
            $donation = Donation::where('donation_uuid', $donation_uuid)->firstOrFail();

            $query = $donation->payments()->with(['user', 'donation']);

            // Filter by payment status
            if ($status && $status !== 'all') {
                $query->where('payment_status', $status);
            }

            // Order by most recent first
            $query->orderBy('created_at', 'desc');

            $payments = $query->paginate($perPage);

            // Calculate statistics
            $statistics = [
                'total_contributions' => $donation->payments()->count(),
                'completed_contributions' => $donation->completedPayments()->count(),
                'pending_contributions' => $donation->payments()->where('payment_status', 'pending')->count(),
                'failed_contributions' => $donation->payments()->where('payment_status', 'failed')->count(),
                'total_raised' => $donation->completedPayments()->sum('amount'),
                'target_amount' => $donation->target_amount,
                'progress_percentage' => $donation->target_amount > 0
                    ? round(($donation->completedPayments()->sum('amount') / $donation->target_amount) * 100, 2)
                    : 0,
            ];

            return ApiResponse::success([
                'donation' => $donation,
                'contributions' => $payments,
                'statistics' => $statistics,
            ], 'Contributions retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Donation not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve contributions', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
