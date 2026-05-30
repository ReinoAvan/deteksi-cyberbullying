<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $fillable = [
        'student_id',
        'name',
        'response_time_mean',
        'empathy_score',
        'conformity_index',
        'aggression_score',
        'emotion_stability',
        'anonymity_effect',
        'final_empathy',
        'risk_score',
        'risk_label',
        'last_update',
    ];

    protected $casts = [
        'response_time_mean' => 'float',
        'empathy_score' => 'float',
        'conformity_index' => 'float',
        'aggression_score' => 'float',
        'emotion_stability' => 'float',
        'anonymity_effect' => 'float',
        'final_empathy' => 'float',
        'risk_score' => 'float',
        'risk_label' => 'integer',
        'last_update' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'nis');
    }
}
