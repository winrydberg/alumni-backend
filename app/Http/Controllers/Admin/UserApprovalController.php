<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationApproved;
use App\Mail\ApplicationRejected;

class UserApprovalController extends Controller
{
    /**
     * Get all non-approved users
     */
    public function getPendingUsers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $pendingUsers = User::where('is_approved', false)
                ->where('is_verified', true) // Only show verified users
                ->whereNull('rejected_at')
                ->with(['degrees', 'contactInfo', 'currentEmployment'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success($pendingUsers, 'Pending users retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve pending users', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Approve a single user
     */
    public function approveUser($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->is_approved) {
                return ApiResponse::error('User is already approved', 400);
            }

            if (!$user->is_verified) {
                return ApiResponse::error('User must verify email before approval', 400);
            }

            // Generate password if user doesn't have one
            $generatedPassword = null;
            if (empty($user->password)) {
                $generatedPassword = $this->generateEasyPassword();
                $user->password = \Hash::make($generatedPassword);
            }

            $user->is_approved = true;
            $user->is_active = true;
            $user->approved_at = now();
            $user->save();

            // Send approval email with generated password if applicable
            try {
                Mail::to($user->email)->send(new ApplicationApproved($user, $generatedPassword));
            } catch (\Exception $mailException) {
                // Log email error but don't fail the approval
                \Log::error('Failed to send approval email: ' . $mailException->getMessage());
            }

            return ApiResponse::success([
                'user' => $user,
                'password_generated' => !empty($generatedPassword)
            ], 'User approved successfully');
        } catch (\ModelNotFoundException $e) {
            return ApiResponse::error('User not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to approve user', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate an easy-to-remember password
     */
    private function generateEasyPassword()
    {
        $adjectives = ['Happy', 'Bright', 'Smart', 'Quick', 'Bold', 'Wise', 'Calm', 'Eager'];
        $nouns = ['Lion', 'Eagle', 'Tiger', 'Falcon', 'Wolf', 'Bear', 'Hawk', 'Fox'];
        $numbers = rand(100, 999);

        $adjective = $adjectives[array_rand($adjectives)];
        $noun = $nouns[array_rand($nouns)];

        return $adjective . $noun . $numbers;
    }

    /**
     * Approve multiple users
     */
    public function approveMultipleUsers(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        DB::beginTransaction();

        try {
            $users = User::whereIn('id', $request->user_ids)
                ->where('is_verified', true)
                ->where('is_approved', false)
                ->get();

            if ($users->isEmpty()) {
                return ApiResponse::error('No eligible users found for approval', 400);
            }

            $approvedCount = 0;
            foreach ($users as $user) {
                // Generate password if user doesn't have one
                $generatedPassword = null;
                if (empty($user->password)) {
                    $generatedPassword = $this->generateEasyPassword();
                    $user->password = \Hash::make($generatedPassword);
                }

                $user->is_approved = true;
                $user->is_active = true;
                $user->approved_at = now();
                $user->save();

                // Send approval email to each user with generated password if applicable
                try {
                    Mail::to($user->email)->send(new ApplicationApproved($user, $generatedPassword));
                } catch (\Exception $mailException) {
                    \Log::error('Failed to send approval email to user ' . $user->id . ': ' . $mailException->getMessage());
                }

                $approvedCount++;
            }

            DB::commit();

            return ApiResponse::success([
                'approved_count' => $approvedCount,
                'users' => $users
            ], "{$approvedCount} user(s) approved successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to approve users', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reject a user
     */
    public function rejectUser($id, Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        try {
            $user = User::findOrFail($id);

            if ($user->is_approved) {
                return ApiResponse::error('Cannot reject an already approved user', 400);
            }

            $user->is_active = false;
            $user->rejection_reason = $request->reason;
            $user->rejected_at = now();
            $user->save();

            // Send rejection email with reason
            try {
                Mail::to($user->email)->send(new ApplicationRejected($user, $request->reason));
            } catch (\Exception $mailException) {
                // Log email error but don't fail the rejection
                \Log::error('Failed to send rejection email: ' . $mailException->getMessage());
            }

            return ApiResponse::success([
                'user' => $user
            ], 'User rejected successfully');
        } catch (\ModelNotFoundException $e) {
            return ApiResponse::error('User not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to reject user', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all approved users
     */
    public function getApprovedUsers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = User::where('is_approved', true)
                ->where('is_verified', true)
                ->with(['degrees', 'contactInfo', 'currentEmployment']);

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $approvedUsers = $query->orderBy('approved_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success($approvedUsers, 'Approved users retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve approved users', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all rejected users
     */
    public function getRejectedUsers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = User::whereNotNull('rejected_at')
                ->where('is_approved', false)
                ->with(['degrees', 'contactInfo', 'currentEmployment']);

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $rejectedUsers = $query->orderBy('rejected_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success($rejectedUsers, 'Rejected users retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve rejected users', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all alumni applications (pending, approved, rejected)
     */
    public function getAllApplications(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status'); // pending, approved, rejected, all

            $query = User::where('is_verified', true)
                ->with(['degrees', 'contactInfo', 'currentEmployment']);

            // Filter by status
            if ($status === 'pending') {
                $query->where('is_approved', false)->whereNull('rejected_at');
            } elseif ($status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($status === 'rejected') {
                $query->whereNotNull('rejected_at')->where('is_approved', false);
            }
            // If status is 'all' or not provided, get all verified users

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $applications = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success($applications, 'Applications retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve applications', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
