<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDonationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\UserChapterController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\Admin\AccountsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\ChapterManagementController;
use App\Http\Controllers\Admin\CountryChapterConfigurationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/alumni/register', [AuthController::class, 'register']);
Route::post('/alumni/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/alumni/verify-email/{token}/{email}', [AuthController::class, 'verifyEmail']);
Route::post('/alumni/login', [AuthController::class, 'login']);

// Hall routes (public - for registration form)
Route::get('/halls', [HallController::class, 'index']);
Route::get('/alumni/chapters/country/{country_code}', [UserChapterController::class, 'getChaptersByCountry']);



// Password reset routes (public)
Route::post('/alumni/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/alumni/verify-reset-token', [PasswordResetController::class, 'verifyResetToken']);
Route::post('/alumni/reset-password', [PasswordResetController::class, 'resetPassword']);

// Protected routes (Passport authentication required)
Route::middleware('auth:api')->group(function () {
    Route::post('/alumni/logout', [AuthController::class, 'logout']);

    // Alumni profile routes
    Route::get('/alumni/profile', [ProfileController::class, 'getProfile']);
    Route::put('/alumni/profile', [ProfileController::class, 'updateProfile']);

    // Password management routes
    Route::post('/alumni/change-password', [ProfileController::class, 'changePassword']);
    Route::post('/alumni/set-password', [ProfileController::class, 'setPassword']);

    // Alumni donation routes
    Route::get('/alumni/donations/featured', [UserDonationController::class, 'getFeaturedDonations']);
    Route::get('/alumni/donations/categories', [UserDonationController::class, 'getCategories']);
    Route::get('/alumni/donations/statistics', [UserDonationController::class, 'getDonationStatistics']);
    Route::get('/alumni/donations', [UserDonationController::class, 'getAllDonations']);
    Route::get('/alumni/donations/{id}', [UserDonationController::class, 'getDonation']);

    // Alumni chapter routes
    Route::get('/alumni/chapters', [UserChapterController::class, 'getAllChapters']);
    Route::get('/alumni/chapters/available', [UserChapterController::class, 'getAvailableChapters']);
    Route::get('/alumni/chapters/my-chapter', [UserChapterController::class, 'getMyChapter']);
    Route::get('/alumni/chapters/{id}', [UserChapterController::class, 'getChapterDetails']);
    Route::post('/alumni/chapters/join', [UserChapterController::class, 'joinChapter']);
    Route::post('/alumni/chapters/leave', [UserChapterController::class, 'leaveChapter']);

    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Add more protected routes here
    // Route::get('/alumni/profile', [ProfileController::class, 'show']);
    // Route::put('/alumni/profile', [ProfileController::class, 'update']);
});


Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);
// Protected admin routes
Route::middleware(['auth:admin', 'admin.scope'])->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'adminLogout']);

    // Alumni application management routes
    Route::get('/alumni/pending', [UserApprovalController::class, 'getPendingUsers']);
    Route::get('/alumni/approved', [UserApprovalController::class, 'getApprovedUsers']);
    Route::get('/alumni/rejected', [UserApprovalController::class, 'getRejectedUsers']);
    Route::get('/alumni/applications', [UserApprovalController::class, 'getAllApplications']);
    Route::post('/alumni/{id}/approve', [UserApprovalController::class, 'approveUser']);
    Route::post('/alumni/approve-multiple', [UserApprovalController::class, 'approveMultipleUsers']);
    Route::post('/alumni/{id}/reject', [UserApprovalController::class, 'rejectUser']);

    Route::get('/admin/user', function (Request $request) {
        return $request->user();
    });

    // Admin accounts management routes
    Route::get('/accounts', [AccountsController::class, 'getAllAdmins']);
    Route::get('/accounts/{id}', [AccountsController::class, 'getAdmin']);
    Route::post('/accounts', [AccountsController::class, 'createAdmin']);
    Route::put('/accounts/{id}', [AccountsController::class, 'updateAdmin']);
    Route::delete('/accounts/{id}', [AccountsController::class, 'deleteAdmin']);

    // Dashboard routes
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData']);
    Route::get('/dashboard/trends', [DashboardController::class, 'getApplicationTrends']);

    // Donation management routes
    Route::get('/donations', [DonationController::class, 'index']);
    Route::get('/donations/categories', [DonationController::class, 'getCategories']);
    Route::get('/donations/statistics', [DonationController::class, 'getStatistics']);
    Route::get('/donations/{donation_uuid}/contributions', [DonationController::class, 'getContributions']);
    Route::get('/donations/{id}', [DonationController::class, 'show']);
    Route::post('/donations', [DonationController::class, 'store']);
    Route::post('/donations/{id}', [DonationController::class, 'update']); // Changed from PUT to POST for form-data support
    Route::delete('/donations/{id}', [DonationController::class, 'destroy']);
    Route::post('/donations/{id}/restore', [DonationController::class, 'restore']);
    Route::post('/donations/{id}/toggle-status', [DonationController::class, 'toggleStatus']);
    Route::post('/donations/{id}/toggle-featured', [DonationController::class, 'toggleFeatured']);

    // Chapter management routes
    Route::get('/chapters', [ChapterManagementController::class, 'index']);
    Route::get('/chapters/statistics', [ChapterManagementController::class, 'statistics']);
    Route::get('/chapters/{id}', [ChapterManagementController::class, 'show']);
    Route::get('/chapters/{id}/members', [ChapterManagementController::class, 'getMembers']);
    Route::post('/chapters', [ChapterManagementController::class, 'store']);
    Route::put('/chapters/{id}', [ChapterManagementController::class, 'update']);
    Route::delete('/chapters/{id}', [ChapterManagementController::class, 'destroy']);

    // Country chapter configuration routes
    Route::get('/country-configurations', [CountryChapterConfigurationController::class, 'index']);
    Route::get('/country-configurations/{countryCode}', [CountryChapterConfigurationController::class, 'show']);
    Route::post('/country-configurations', [CountryChapterConfigurationController::class, 'store']);
    Route::delete('/country-configurations/{countryCode}', [CountryChapterConfigurationController::class, 'destroy']);
});
