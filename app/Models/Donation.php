<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'donation_uuid',
        'title',
        'description',
        'target_amount',
        'minimum_amount',
        'category',
        'start_date',
        'end_date',
        'is_active',
        'is_featured',
        'image_url',
        'payment_methods',
        'terms_and_conditions',
        'created_by',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'payment_methods' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the admin who created this donation
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get all payments for this donation
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get completed payments for this donation
     */
    public function completedPayments()
    {
        return $this->hasMany(Payment::class)->where('payment_status', 'completed');
    }

    /**
     * Get total amount raised for this donation
     */
    public function getTotalRaisedAttribute()
    {
        return $this->completedPayments()->sum('amount');
    }

    /**
     * Check if donation is currently active
     */
    public function isCurrentlyActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Scope to get only active donations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope to get featured donations
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
