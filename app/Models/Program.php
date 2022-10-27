<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
  protected $fillable = [
    'id', 'start', 'stop'
  ];
  public static function getNumOfHours()
  {
    return Program::count();
  }
  public function getActiveHour($value)
  {
    return Program::where('start', '<=', $value)->where('stop', '>=', $value)->orderby('id', 'DESC')->first()->id ?? null;
  }
}
