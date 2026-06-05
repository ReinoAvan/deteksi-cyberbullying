<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'nama_sikap',
        'uraian_rekomendasi',
    ];
}
