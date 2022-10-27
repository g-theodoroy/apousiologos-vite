<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'anathesi_id', 'student_id', 'period_id', 'grade'
    ];
}
