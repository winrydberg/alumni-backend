<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\Donation;
use App\Models\Payment;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            // Check if filtering by specific donation
            $donationId = $request->input('donation_id');
            $donation = null;

            if ($donationId) {
                $donation = Donation::find($donationId);
                if (!$donation) {
                    return ApiResponse::error('Donation not found', 404);
                }
            }

            // Total applications (verified users only)
            $totalApplications = User::where('is_verified', true)->count();

            // Approved applications
            $approvedCount = User::where('is_approved', true)
                ->where('is_verified', true)
                ->count();

            // Rejected applications
            $rejectedCount = User::whereNotNull('rejected_at')
                ->where('is_approved', false)
                ->where('is_verified', true)
                ->count();

            // Pending applications
            $pendingCount = User::where('is_approved', false)
                ->where('is_verified', true)
                ->whereNull('rejected_at')
                ->count();

            // Total admin accounts
            $totalAdmins = Admin::count();

            // Donation and Payment Statistics - filtered by donation_id if provided
            if ($donationId) {
                // Filter for specific donation
                $totalDonations = 1;
                $activeDonations = $donation->is_active ? 1 : 0;
                $totalPayments = Payment::where('payment_status', 'completed')
                    ->where('donation_id', $donationId)
                    ->count();
                $totalAmountRaised = Payment::where('payment_status', 'completed')
                    ->where('donation_id', $donationId)
                    ->sum('amount');
            } else {
                // Get all donations statistics
                $totalDonations = Donation::count();
                $activeDonations = Donation::where('is_active', true)->count();
                $totalPayments = Payment::where('payment_status', 'completed')->count();
                $totalAmountRaised = Payment::where('payment_status', 'completed')->sum('amount');
            }

            // Get payment data for each donation (for charts) - filtered if donation_id provided
            $donationPaymentsQuery = Donation::select(
                'donations.id',
                'donations.title',
                'donations.target_amount',
                DB::raw('COUNT(payments.id) as payment_count'),
                DB::raw('COALESCE(SUM(CASE WHEN payments.payment_status = "completed" THEN payments.amount ELSE 0 END), 0) as total_raised')
            )
            ->leftJoin('payments', 'donations.id', '=', 'payments.donation_id')
            ->groupBy('donations.id', 'donations.title', 'donations.target_amount');

            if ($donationId) {
                $donationPaymentsQuery->where('donations.id', $donationId);
            } else {
                $donationPaymentsQuery->orderBy('total_raised', 'desc')->limit(10); // Top 10 donations
            }

            $donationPayments = $donationPaymentsQuery->get();

            // Format data for ApexCharts - Bar/Column Chart
            $donationChartData = [
                'categories' => $donationPayments->pluck('title')->toArray(),
                'series' => [
                    [
                        'name' => 'Amount Raised',
                        'data' => $donationPayments->pluck('total_raised')->map(function($value) {
                            return (float) $value;
                        })->toArray()
                    ],
                    [
                        'name' => 'Target Amount',
                        'data' => $donationPayments->pluck('target_amount')->map(function($value) {
                            return (float) $value;
                        })->toArray()
                    ]
                ]
            ];

            // Payment trends by month (last 6 months) - filtered if donation_id provided
            $paymentTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $startDate = now()->subMonths($i)->startOfMonth();
                $endDate = now()->subMonths($i)->endOfMonth();

                $monthQuery = Payment::where('payment_status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate]);

                if ($donationId) {
                    $monthQuery->where('donation_id', $donationId);
                }

                $monthData = $monthQuery->selectRaw('COUNT(*) as count, SUM(amount) as total')->first();

                $paymentTrends[] = [
                    'month' => $startDate->format('M Y'),
                    'payment_count' => $monthData->count ?? 0,
                    'total_amount' => (float) ($monthData->total ?? 0)
                ];
            }

            // Format payment trends for ApexCharts - Line/Area Chart
            $paymentTrendsChart = [
                'categories' => array_column($paymentTrends, 'month'),
                'series' => [
                    [
                        'name' => 'Payment Count',
                        'data' => array_column($paymentTrends, 'payment_count')
                    ],
                    [
                        'name' => 'Total Amount',
                        'data' => array_column($paymentTrends, 'total_amount')
                    ]
                ]
            ];

            // Payment status distribution (for Pie/Donut chart) - filtered if donation_id provided
            $paymentStatusQuery = Payment::select('payment_status', DB::raw('COUNT(*) as count'));

            if ($donationId) {
                $paymentStatusQuery->where('donation_id', $donationId);
            }

            $paymentStatusDistribution = $paymentStatusQuery->groupBy('payment_status')->get();

            $paymentStatusChart = [
                'labels' => $paymentStatusDistribution->pluck('payment_status')->toArray(),
                'series' => $paymentStatusDistribution->pluck('count')->toArray()
            ];

            // Top donors (for leaderboard/table) - filtered if donation_id provided
            $topDonorsQuery = Payment::select(
                'donor_name',
                'donor_email',
                DB::raw('COUNT(*) as donation_count'),
                DB::raw('SUM(amount) as total_donated')
            )
            ->where('payment_status', 'completed')
            ->whereNotNull('donor_name');

            if ($donationId) {
                $topDonorsQuery->where('donation_id', $donationId);
            }

            $topDonors = $topDonorsQuery
                ->groupBy('donor_name', 'donor_email')
                ->orderBy('total_donated', 'desc')
                ->limit(5)
                ->get()
                ->map(function($donor) {
                    return [
                        'donor_name' => $donor->donor_name,
                        'donor_email' => $donor->donor_email,
                        'donation_count' => $donor->donation_count,
                        'total_donated' => (float) $donor->total_donated
                    ];
                });

            $statistics = [
                'total_applications' => $totalApplications,
                'approved_applications' => $approvedCount,
                'rejected_applications' => $rejectedCount,
                'pending_applications' => $pendingCount,
                'total_admin_accounts' => $totalAdmins,

                // Donation & Payment stats
                'total_donations' => $totalDonations,
                'active_donations' => $activeDonations,
                'total_payments' => $totalPayments,
                'total_amount_raised' => (float) $totalAmountRaised,

                // Chart data for React ApexCharts
                'charts' => [
                    'donation_performance' => $donationChartData,
                    'payment_trends' => $paymentTrendsChart,
                    'payment_status_distribution' => $paymentStatusChart,
                ],

                // Additional data
                'top_donors' => $topDonors,
            ];

            // Add donation info if filtered by specific donation
            if ($donationId && $donation) {
                $statistics['filtered_by_donation'] = [
                    'id' => $donation->id,
                    'title' => $donation->title,
                    'donation_uuid' => $donation->donation_uuid,
                    'target_amount' => (float) $donation->target_amount,
                    'is_active' => $donation->is_active,
                ];
            }

            return ApiResponse::success($statistics, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve dashboard statistics', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get detailed dashboard data with recent applications
     */
    public function getDashboardData(Request $request)
    {
        try {
            // Get statistics
            $totalApplications = User::where('is_verified', true)->count();
            $approvedCount = User::where('is_approved', true)->where('is_verified', true)->count();
            $rejectedCount = User::whereNotNull('rejected_at')->where('is_approved', false)->where('is_verified', true)->count();
            $pendingCount = User::where('is_approved', false)->where('is_verified', true)->whereNull('rejected_at')->count();

            // Get recent pending applications (last 5)
            $recentPending = User::where('is_approved', false)
                ->where('is_verified', true)
                ->whereNull('rejected_at')
                ->with(['degrees'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Get recent approved applications (last 5)
            $recentApproved = User::where('is_approved', true)
                ->where('is_verified', true)
                ->with(['degrees'])
                ->orderBy('approved_at', 'desc')
                ->limit(5)
                ->get();

            // Get recent rejected applications (last 5)
            $recentRejected = User::whereNotNull('rejected_at')
                ->where('is_approved', false)
                ->with(['degrees'])
                ->orderBy('rejected_at', 'desc')
                ->limit(5)
                ->get();

            $dashboardData = [
                'statistics' => [
                    'total_applications' => $totalApplications,
                    'approved_applications' => $approvedCount,
                    'rejected_applications' => $rejectedCount,
                    'pending_applications' => $pendingCount,
                ],
                'recent_pending' => $recentPending,
                'recent_approved' => $recentApproved,
                'recent_rejected' => $recentRejected,
            ];

            return ApiResponse::success($dashboardData, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve dashboard data', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get application trends (monthly data for charts)
     */
    public function getApplicationTrends(Request $request)
    {
        try {
            $months = $request->input('months', 6); // Default to last 6 months

            $trends = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $startDate = now()->subMonths($i)->startOfMonth();
                $endDate = now()->subMonths($i)->endOfMonth();

                $monthData = [
                    'month' => $startDate->format('M Y'),
                    'total' => User::where('is_verified', true)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count(),
                    'approved' => User::where('is_approved', true)
                        ->whereBetween('approved_at', [$startDate, $endDate])
                        ->count(),
                    'rejected' => User::whereNotNull('rejected_at')
                        ->whereBetween('rejected_at', [$startDate, $endDate])
                        ->count(),
                    'pending' => User::where('is_approved', false)
                        ->whereNull('rejected_at')
                        ->where('is_verified', true)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->count(),
                ];

                $trends[] = $monthData;
            }

            return ApiResponse::success([
                'trends' => $trends
            ], 'Application trends retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve application trends', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
