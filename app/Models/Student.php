<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'nis',
        'class',
        'gender',
        'status',
        'risk_level',
        'risk_score'
    ];
}