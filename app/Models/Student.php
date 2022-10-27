<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
  protected $fillable = [
    'id', 'eponimo', 'onoma', 'patronimo', 'email'
  ];
  public function tmimata()
  {
    return $this->hasMany('App\Models\Tmima');
  }
  public static function getNumOfStudents()
  {
    return Student::count();
  }
  public function apousies()
  {
    return $this->hasMany('App\Models\Apousie');
  }
  public function anatheseis()
  {
    return $this->belongsToMany(Anathesi::class, 'grades')->withPivot('grade', 'period_id');
  }
}
