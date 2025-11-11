<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile with all associated data
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Load all relationships
            $user->load([
                'contactInfo',
                'degrees' => function($query) {
                    $query->orderBy('is_primary', 'desc')
                          ->orderBy('year_of_completion', 'desc');
                },
                'employmentInfo' => function($query) {
                    $query->orderBy('is_active', 'desc')
                          ->orderBy('employment_start_date', 'desc');
                },
                'currentEmployment'
            ]);

            return ApiResponse::success([
                'user' => $user
            ], 'Profile retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve profile: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve profile', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update the authenticated user's profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Validate the request
            $validator = \Validator::make($request->all(), [
                'title' => 'nullable|string|max:10',
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'other_names' => 'nullable|string|max:255',
                'maiden_name' => 'nullable|string|max:255',
                'dob' => 'sometimes|required|date|before:today',
                'nationality' => 'nullable|string|max:255',
                'country_of_residence' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'phone_number' => 'sometimes|required|string|max:20|unique:users,phone_number,' . $user->id,
                'hall_of_residence' => 'nullable|string|max:255',
                'linkedin_profile' => 'nullable|url|max:500',
                'personal_website' => 'nullable|url|max:500',
                'share_with_alumni_associations' => 'nullable|boolean',
                'include_in_birthday_list' => 'nullable|boolean',
                'receive_newsletter' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            // Update user profile
            $user->update($request->only([
                'title',
                'first_name',
                'last_name',
                'other_names',
                'maiden_name',
                'dob',
                'nationality',
                'country_of_residence',
                'bio',
                'phone_number',
                'hall_of_residence',
                'linkedin_profile',
                'personal_website',
                'share_with_alumni_associations',
                'include_in_birthday_list',
                'receive_newsletter',
            ]));

            // Reload relationships
            $user->load(['contactInfo', 'degrees', 'employmentInfo', 'currentEmployment']);

            return ApiResponse::success([
                'user' => $user
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to update profile: ' . $e->getMessage());

            return ApiResponse::error('Failed to update profile', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Change the authenticated user's password
     */
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validate the request
            $validator = \Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            // Check if user has a password (in case they registered without one)
            if (empty($user->password)) {
                return ApiResponse::error('You do not have a current password set. Please contact support.', 400);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return ApiResponse::error('Current password is incorrect', 401);
            }

            // Check if new password is different from current password
            if (Hash::check($request->new_password, $user->password)) {
                return ApiResponse::error('New password must be different from current password', 400);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return ApiResponse::success([], 'Password changed successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to change password: ' . $e->getMessage());

            return ApiResponse::error('Failed to change password', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Set password for users who registered without one
     */
    public function setPassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validate the request
            $validator = \Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            // Check if user already has a password
            if (!empty($user->password)) {
                return ApiResponse::error('You already have a password set. Use change password instead.', 400);
            }

            // Set password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return ApiResponse::success([], 'Password set successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to set password: ' . $e->getMessage());

            return ApiResponse::error('Failed to set password', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
