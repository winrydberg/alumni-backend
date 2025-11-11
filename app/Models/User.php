<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ["id"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'share_with_alumni_associations' => 'boolean',
            'include_in_birthday_list' => 'boolean',
            'receive_newsletter' => 'boolean',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the contact info for the user.
     */
    public function contactInfo()
    {
        return $this->hasOne(ContactInfo::class);
    }

    /**
     * Get the degrees for the user.
     */
    public function degrees()
    {
        return $this->hasMany(StudentDegree::class);
    }

    /**
     * Get the primary degree for the user.
     */
    public function primaryDegree()
    {
        return $this->hasOne(StudentDegree::class)->where('is_primary', true);
    }

    /**
     * Get the employment info for the user.
     */
    public function employmentInfo()
    {
        return $this->hasMany(EmploymentInfo::class);
    }

    /**
     * Get the current employment info.
     */
    public function currentEmployment()
    {
        return $this->hasMany(EmploymentInfo::class);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        $names = array_filter([
            $this->title,
            $this->first_name,
            $this->other_names,
            $this->last_name,
        ]);

        return implode(' ', $names);
    }

    /**
     * Get the user's primary chapter
     */
    public function chapter()
    {
        return $this->belongsToMany(Chapter::class, 'chapter_user')
            ->withPivot('is_primary', 'membership_status', 'joined_at')
            ->wherePivot('is_primary', true)
            ->wherePivot('membership_status', 'active')
            ->withTimestamps()
            ->first();
    }

    /**
     * Get all chapters user belongs to
     */
    public function chapters()
    {
        return $this->belongsToMany(Chapter::class, 'chapter_user')
            ->withPivot('is_primary', 'membership_status', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Assign user to a chapter
     */
    public function assignToChapter($chapterId, $isPrimary = true)
    {
        // If setting as primary, unset other primary chapters
        if ($isPrimary) {
            $this->chapters()->updateExistingPivot(
                $this->chapters()->pluck('chapters.id')->toArray(),
                ['is_primary' => false]
            );
        }

        $this->chapters()->attach($chapterId, [
            'is_primary' => $isPrimary,
            'membership_status' => 'active',
            'joined_at' => now(),
        ]);

        return true;
    }

    /**
     * Get suggested chapter based on residence
     */
    public function getSuggestedChapter()
    {
        if (!$this->country_of_residence) {
            return null;
        }

        $config = CountryChapterConfiguration::where('country_code', $this->country_of_residence)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return null;
        }

        if ($config->usesCountryChapter()) {
            return Chapter::where('country_code', $this->country_of_residence)
                ->where('type', 'country')
                ->active()
                ->first();
        }

        // City-based chapter
        if ($this->city_of_residence) {
            return Chapter::where('country_code', $this->country_of_residence)
                ->where('type', 'city')
                ->where('city', $this->city_of_residence)
                ->active()
                ->first();
        }

        return null;
    }

    /**
     * Scope a query to only include verified users.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include approved users.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }



}
