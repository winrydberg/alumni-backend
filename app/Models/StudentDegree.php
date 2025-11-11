<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDegree extends Model
{

    protected $guarded = ['id'];

    /**
     * Get the user that owns the student degree.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
