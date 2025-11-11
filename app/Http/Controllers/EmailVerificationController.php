<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Responses\ApiResponse;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return ApiResponse::error('Verification token is required', 400);
        }

        $user = User::where('email_verification_token', $token)->first();
        if (!$user) {
            return ApiResponse::error('Invalid or expired verification token', 404);
        }

        $user->is_verified = true;
        $user->is_active = true;
        $user->email_verification_token = null;
        $user->save();

        return ApiResponse::success(null, 'Email verified successfully');
    }
}

