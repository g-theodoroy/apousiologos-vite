<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'value'];

    public static function getValueOf($key)
    {
        if (Schema::hasTable('settings')) {
            $c = null;
            $c = Setting::firstOrCreate(['key' => $key]);
            if ($c) {
                return $c->value;
            } else {
                return null;
            }
        }
        return null;
    }

    public static function setValueOf($key, $value)
    {
        $c = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        return;
    }

    public static function getValues()
    {
        if(! Schema::hasTable('settings')) return null;
        $confs =  Setting::all();
        $Settings = [];
        foreach ($confs as $conf) {
            $Settings[$conf->key] = $conf->value;
        }
        return $Settings;
    }
}
