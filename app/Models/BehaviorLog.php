<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorLog extends Model
{
    protected $fillable = [
        'student_name',
        'date',
        'level',
        'response',
        'indicator',
        'indicator_value',
        'risk_category'
    ];
}