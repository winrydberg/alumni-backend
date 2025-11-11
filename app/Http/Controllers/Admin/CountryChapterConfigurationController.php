<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryChapterConfiguration;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;

class CountryChapterConfigurationController extends Controller
{
    /**
     * Get all country configurations
     */
    public function index()
    {
        try {
            $configurations = CountryChapterConfiguration::with('chapters')
                ->orderBy('country_name')
                ->get();

            return ApiResponse::success($configurations, 'Country configurations retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve country configurations: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve country configurations', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create or update country configuration
     */
    public function store(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'country_code' => 'required|string|size:2',
                'country_name' => 'required|string',
                'chapter_type' => 'required|in:country,city',
                'allow_multiple_chapters' => 'boolean',
                'is_active' => 'boolean',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $config = CountryChapterConfiguration::updateOrCreate(
                ['country_code' => $request->country_code],
                $request->all()
            );

            return ApiResponse::success($config, 'Country configuration saved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to save country configuration: ' . $e->getMessage());
            return ApiResponse::error('Failed to save country configuration', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get configuration for a specific country
     */
    public function show($countryCode)
    {
        try {
            $config = CountryChapterConfiguration::where('country_code', $countryCode)
                ->with('chapters')
                ->firstOrFail();

            return ApiResponse::success([
                'configuration' => $config
            ], 'Country configuration retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Country configuration not found', 404);
        }
    }

    /**
     * Delete country configuration
     */
    public function destroy($countryCode)
    {
        try {
            $config = CountryChapterConfiguration::where('country_code', $countryCode)->firstOrFail();

            if ($config->chapters()->count() > 0) {
                return ApiResponse::error('Cannot delete configuration with existing chapters', 400);
            }

            $config->delete();

            return ApiResponse::success([], 'Country configuration deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete country configuration', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

