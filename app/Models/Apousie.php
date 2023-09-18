<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apousie extends Model
{
    protected $fillable = [
        'student_id',
        'date',
        'apousies',
        'apovoles',
        'teachers'
    ];
    public function student()
    {
        return $this->belongsTo('App\Models\Student');
    }
    public function apousiesDaysCount()
    {
        return Apousie::distinct('date')->count();
    }
}
