<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Responses\ApiResponse;

class AdminAuthController extends Controller
{

    public function adminLogin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error', 422, $validator->errors());
        }

        // Find admin by username
        $admin = Admin::where('username', $request->username)->first();

        // Check if admin exists and password matches
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        // Create API token with admin scope
        $token = $admin->createToken('Admin API Token', ['admin'])->accessToken;

        return ApiResponse::success([
            'admin' => $admin,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function adminLogout(Request $request)
    {
        $request->user()->token()->revoke();
        return ApiResponse::success([], 'Successfully logged out');
    }

}
