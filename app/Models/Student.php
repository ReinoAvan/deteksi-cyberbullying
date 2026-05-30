<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'profile_photo',
        'name',
        'nis',
        'class',
        'gender',
        'status',
        'risk_level',
        'risk_score'
    ];

    public function logActivities()
    {
        return $this->hasMany(LogActivity::class, 'student_id', 'nis');
    }
}
