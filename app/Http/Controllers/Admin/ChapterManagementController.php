<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\CountryChapterConfiguration;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;

class ChapterManagementController extends Controller
{
    /**
     * Get all chapters
     */
    public function index(Request $request)
    {
        try {
            $query = Chapter::with('countryConfiguration');

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by country
            if ($request->has('country_code')) {
                $query->where('country_code', $request->country_code);
            }

            // Filter by status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('country_name', 'like', "%{$search}%");
                });
            }

            $chapters = $query->withCount('activeMembers')
                ->orderBy('country_name')
                ->orderBy('name')
                ->paginate($request->get('per_page', 15));

            return ApiResponse::success($chapters, 'Chapters retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve chapters: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve chapters', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a new chapter
     */
    public function store(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:chapters,code',
                'description' => 'nullable|string',
                'type' => 'required|in:country,city',
                'country_code' => 'required|string|size:2',
                'country_name' => 'required|string',
                'state_province' => 'required_if:type,city|nullable|string',
                'city' => 'required_if:type,city|nullable|string',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $chapter = Chapter::create($request->all());

            return ApiResponse::success($chapter, 'Chapter created successfully', 201);

        } catch (\Exception $e) {
            \Log::error('Failed to create chapter: ' . $e->getMessage());
            return ApiResponse::error('Failed to create chapter', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get a single chapter
     */
    public function show($id)
    {
        try {
            $chapter = Chapter::with(['countryConfiguration', 'activeMembers'])
                ->withCount('activeMembers')
                ->findOrFail($id);

            return ApiResponse::success([
                'chapter' => $chapter
            ], 'Chapter retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Chapter not found', 404);
        }
    }

    /**
     * Update a chapter
     */
    public function update(Request $request, $id)
    {
        try {
            $chapter = Chapter::findOrFail($id);

            $validator = \Validator::make($request->all(), [
                'name' => 'string|max:255',
                'code' => 'string|max:50|unique:chapters,code,' . $chapter->id,
                'description' => 'nullable|string',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $chapter->update($request->all());

            return ApiResponse::success($chapter, 'Chapter updated successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to update chapter: ' . $e->getMessage());
            return ApiResponse::error('Failed to update chapter', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a chapter
     */
    public function destroy($id)
    {
        try {
            $chapter = Chapter::findOrFail($id);

            if ($chapter->activeMembers()->count() > 0) {
                return ApiResponse::error('Cannot delete chapter with active members', 400);
            }

            $chapter->delete();

            return ApiResponse::success([], 'Chapter deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete chapter', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get chapter statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_chapters' => Chapter::count(),
                'active_chapters' => Chapter::active()->count(),
                'country_chapters' => Chapter::countryBased()->count(),
                'city_chapters' => Chapter::cityBased()->count(),
                'total_members' => \DB::table('chapter_user')
                    ->where('membership_status', 'active')
                    ->distinct('user_id')
                    ->count(),
                'chapters_by_country' => Chapter::selectRaw('country_code, country_name, COUNT(*) as count')
                    ->groupBy('country_code', 'country_name')
                    ->get(),
            ];

            return ApiResponse::success($stats, 'Statistics retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve statistics: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve statistics', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get chapter members
     */
    public function getMembers($id, Request $request)
    {
        try {
            $chapter = Chapter::findOrFail($id);

            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = $chapter->activeMembers();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $members = $query->paginate($perPage);

            return ApiResponse::success($members, 'Chapter members retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve chapter members: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve chapter members', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

