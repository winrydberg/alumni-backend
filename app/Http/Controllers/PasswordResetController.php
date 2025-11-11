<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Responses\ApiResponse;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link to user's email
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $user = User::where('email', $request->email)->first();

            // Check if user is approved and active
            if (!$user->is_approved) {
                return ApiResponse::error('Your account is pending approval. Please wait for admin approval.', 403);
            }

            if (!$user->is_active) {
                return ApiResponse::error('Your account is not active. Please contact support.', 403);
            }

            // Generate reset token
            $token = Str::random(64);

            // Delete any existing reset tokens for this user
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Create new reset token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            // Send password reset email
            try {
                Mail::to($user->email)->send(new PasswordResetEmail($user, $token));
            } catch (\Exception $mailException) {
                \Log::error('Failed to send password reset email: ' . $mailException->getMessage());
                return ApiResponse::error('Failed to send reset email. Please try again.', 500);
            }

            return ApiResponse::success([], 'Password reset link has been sent to your email');

        } catch (\Exception $e) {
            \Log::error('Forgot password error: ' . $e->getMessage());
            return ApiResponse::error('Failed to process request', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify reset token
     */
    public function verifyResetToken(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return ApiResponse::error('Invalid or expired reset token', 400);
            }

            // Check if token is expired (1 hour expiration)
            $tokenAge = now()->diffInMinutes($resetRecord->created_at);
            if ($tokenAge > 60) {
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();
                return ApiResponse::error('Reset token has expired. Please request a new one.', 400);
            }

            // Verify token
            if (!Hash::check($request->token, $resetRecord->token)) {
                return ApiResponse::error('Invalid reset token', 400);
            }

            return ApiResponse::success([
                'valid' => true,
                'email' => $request->email
            ], 'Token is valid');

        } catch (\Exception $e) {
            \Log::error('Verify reset token error: ' . $e->getMessage());
            return ApiResponse::error('Failed to verify token', 500);
        }
    }

    /**
     * Reset password using token
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return ApiResponse::error('Invalid or expired reset token', 400);
            }

            // Check if token is expired (1 hour expiration)
            $tokenAge = now()->diffInMinutes($resetRecord->created_at);
            if ($tokenAge > 60) {
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();
                return ApiResponse::error('Reset token has expired. Please request a new one.', 400);
            }

            // Verify token
            if (!Hash::check($request->token, $resetRecord->token)) {
                return ApiResponse::error('Invalid reset token', 400);
            }

            // Get user and update password
            $user = User::where('email', $request->email)->first();

            // Check if new password is different from old password (if old password exists)
            if ($user->password && Hash::check($request->password, $user->password)) {
                return ApiResponse::error('New password must be different from current password', 400);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the reset token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Optionally revoke all existing tokens for security
            DB::table('oauth_access_tokens')
                ->where('user_id', $user->id)
                ->update(['revoked' => true]);

            return ApiResponse::success([], 'Password has been reset successfully. You can now login with your new password.');

        } catch (\Exception $e) {
            \Log::error('Reset password error: ' . $e->getMessage());
            return ApiResponse::error('Failed to reset password', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

