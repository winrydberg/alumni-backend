<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\CountryChapterConfiguration;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;

class UserChapterController extends Controller
{
    /**
     * Get available chapters for user
     */
    public function getAvailableChapters(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->country_of_residence) {
                return ApiResponse::error('Please update your country of residence first', 400);
            }

            $query = Chapter::where('country_code', $user->country_of_residence)
                ->active();

            // If country uses city chapters, filter by city
            if ($user->city_of_residence) {
                $query->where(function($q) use ($user) {
                    $q->where('type', 'country')
                      ->orWhere(function($q2) use ($user) {
                          $q2->where('type', 'city')
                             ->where('city', $user->city_of_residence);
                      });
                });
            }

            $chapters = $query->get();
            $suggestedChapter = $user->getSuggestedChapter();

            return ApiResponse::success([
                'chapters' => $chapters,
                'suggested_chapter' => $suggestedChapter,
            ], 'Available chapters retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve available chapters: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve available chapters', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Join a chapter
     */
    public function joinChapter(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'chapter_id' => 'required|exists:chapters,id',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $user = $request->user();
            $chapter = Chapter::findOrFail($request->chapter_id);

            // Check if already a member
            if ($user->chapters()->where('chapter_id', $chapter->id)->exists()) {
                return ApiResponse::error('You are already a member of this chapter', 400);
            }

            // Check if user already has a primary chapter (for now, only one chapter allowed)
            if ($user->chapters()->wherePivot('is_primary', true)->exists()) {
                return ApiResponse::error('You are already a member of another chapter. Leave your current chapter first.', 400);
            }

            $user->assignToChapter($chapter->id);

            return ApiResponse::success([
                'chapter' => $chapter
            ], 'Successfully joined chapter');

        } catch (\Exception $e) {
            \Log::error('Failed to join chapter: ' . $e->getMessage());
            return ApiResponse::error('Failed to join chapter', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get user's current chapter
     */
    public function getMyChapter(Request $request)
    {
        try {
            $user = $request->user();
            $chapter = $user->chapter();

            if (!$chapter) {
                return ApiResponse::success([
                    'chapter' => null,
                    'has_chapter' => false
                ], 'User has no chapter membership');
            }

            return ApiResponse::success([
                'chapter' => $chapter,
                'has_chapter' => true
            ], 'Current chapter retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve user chapter: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve user chapter', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Leave current chapter
     */
    public function leaveChapter(Request $request)
    {
        try {
            $user = $request->user();
            $chapter = $user->chapter();

            if (!$chapter) {
                return ApiResponse::error('You are not a member of any chapter', 400);
            }

            $user->chapters()->detach($chapter->id);

            return ApiResponse::success([], 'Successfully left chapter');

        } catch (\Exception $e) {
            \Log::error('Failed to leave chapter: ' . $e->getMessage());
            return ApiResponse::error('Failed to leave chapter', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all chapters (public - for browsing)
     */
    public function getAllChapters(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $countryCode = $request->input('country_code');
            $type = $request->input('type');

            $query = Chapter::active()->withCount('activeMembers');

            if ($countryCode) {
                $query->where('country_code', $countryCode);
            }

            if ($type) {
                $query->where('type', $type);
            }

            $chapters = $query->orderBy('country_name')
                ->orderBy('name')
                ->paginate($perPage);

            return ApiResponse::success($chapters, 'Chapters retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve chapters: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve chapters', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get chapter details
     */
    public function getChapterDetails($id)
    {
        try {
            $chapter = Chapter::active()
                ->withCount('activeMembers')
                ->findOrFail($id);

            return ApiResponse::success([
                'chapter' => $chapter
            ], 'Chapter details retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Chapter not found', 404);
        }
    }


    public function getChaptersByCountry($country_code)
    {
        $chapters = Chapter::where('country_code', $country_code)->where('is_active', true)->get();

        return ApiResponse::success($chapters, 'Chapters retrieved successfully');
    }
}

