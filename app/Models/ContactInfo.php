<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the user that owns the contact info.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
