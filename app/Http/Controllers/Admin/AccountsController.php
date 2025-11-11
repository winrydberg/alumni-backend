<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;

class AccountsController extends Controller
{
    /**
     * Get all admin accounts
     */
    public function getAllAdmins(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = Admin::query();

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }

            // Order by most recent first
            $query->orderBy('created_at', 'desc');

            // Paginate results
            $accounts = $query->paginate($perPage);

            return ApiResponse::success($accounts, 'Admin accounts retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve admin accounts', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get a single admin account by ID
     */
    public function getAdmin($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            return ApiResponse::success([
                'account' => $admin
            ], 'Admin account retrieved successfully');
        } catch (\ModelNotFoundException $e) {
            return ApiResponse::error('Admin account not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve admin account', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a new admin account
     */
    public function createAdmin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins,username|max:255',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        try {
            $admin = Admin::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => \Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            return ApiResponse::success([
                'account' => $admin
            ], 'Admin account created successfully', 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ApiResponse::error('Failed to create admin account', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an admin account
     */
    public function updateAdmin(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'username' => 'nullable|string|unique:admins,username,' . $id . '|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        try {
            $admin = Admin::findOrFail($id);

            if ($request->has('name')) {
                $admin->name = $request->name;
            }

            if ($request->has('username')) {
                $admin->username = $request->username;
            }

            if ($request->has('password')) {
                $admin->password = \Hash::make($request->password);
            }

            $admin->save();

            return ApiResponse::success([
                'account' => $admin
            ], 'Admin account updated successfully');
        } catch (\ModelNotFoundException $e) {
            return ApiResponse::error('Admin account not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update admin account', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete an admin account
     */
    public function deleteAdmin($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            // Prevent deleting the last admin
            if (Admin::count() <= 1) {
                return ApiResponse::error('Cannot delete the last admin account', 400);
            }

            $admin->delete();

            return ApiResponse::success([], 'Admin account deleted successfully');
        } catch (\ModelNotFoundException $e) {
            return ApiResponse::error('Admin account not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete admin account', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
