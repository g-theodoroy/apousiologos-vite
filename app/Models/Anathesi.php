<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Anathesi extends Model
{
  protected $fillable = [
    'user_id', 'tmima', 'mathima'
  ];
  public function users()
  {
    return $this->belongsToMany(User::class);
  }
  public function students()
  {
    return $this->belongsToMany(Student::class, 'grades')->withPivot('grade', 'period_id');
  }

  public static function countMathimata()
  {
    if (Auth::user()->permissions['admin']) return true;

    $mathimata = Anathesi::select('mathima')->where('user_id', Auth::user()->id)->where('mathima', "<>", "")->distinct()->count();

    if ($mathimata) return true;

    return false;
  }
}
