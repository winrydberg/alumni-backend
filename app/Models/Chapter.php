<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chapter extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chapter) {
            if (empty($chapter->chapter_uuid)) {
                $chapter->chapter_uuid = Str::uuid();
            }
        });
    }

    /**
     * Get the users in this chapter
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'chapter_user')
            ->withPivot('is_primary', 'membership_status', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get active members
     */
    public function activeMembers()
    {
        return $this->users()->wherePivot('membership_status', 'active');
    }

    /**
     * Get the country configuration
     */
    public function countryConfiguration()
    {
        return $this->belongsTo(CountryChapterConfiguration::class, 'country_code', 'country_code');
    }

    /**
     * Scope for active chapters
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for country-based chapters
     */
    public function scopeCountryBased($query)
    {
        return $query->where('type', 'country');
    }

    /**
     * Scope for city-based chapters
     */
    public function scopeCityBased($query)
    {
        return $query->where('type', 'city');
    }

    /**
     * Get members count
     */
    public function getMembersCountAttribute()
    {
        return $this->activeMembers()->count();
    }
}

