<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentInfo extends Model
{

    protected $guarded = ['id'];

    /**
     * Get the user that owns the employment info.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
