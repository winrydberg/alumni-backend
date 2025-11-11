<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryChapterConfiguration extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'allow_multiple_chapters' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get chapters for this country
     */
    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'country_code', 'country_code');
    }

    /**
     * Check if country uses city-based chapters
     */
    public function usesCityChapters()
    {
        return $this->chapter_type === 'city';
    }

    /**
     * Check if country uses country-wide chapter
     */
    public function usesCountryChapter()
    {
        return $this->chapter_type === 'country';
    }
}

