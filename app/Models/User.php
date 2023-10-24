<?php

namespace App\Models;

use App\Models\Anathesi;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * με κενό  $description επιστρέφει τo $role_id του ενεργού χρήστη
     */
    public static function getRoleId($description = null)
    {
        if (!$description) return auth()->user()->role_id;
        $roles = config('gth.roles');
        return array_search($description, $roles);
    }

    /**
     * με κενό $id επιστρέφει την $description του ενεργού χρήστη
     */
    public static function getRoleDescription($id = null)
    {
        $roles = config('gth.roles');
        if (!$id) return $roles[auth()->user()->role_id] ?? false;
        return $roles[$id] ?? false;
    }

    public function getNumOfAdmins()
    {
        return User::whereRoleId(1)->count();
    }

    public static function getNumOfKathigites()
    {
        return User::count();
    }

    public function anatheseis()
    {
       return $this->belongsToMany(Anathesi::class);
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'permissions'
    ];

    public function getPermissionsAttribute()
    {
        return [
            'admin' => $this->role_id == 1,
            'teacher' => $this->role_id == 2,
            'student' => $this->role_id == 3,
            'teacherOrAdmin' => $this->role_id < 3,
        ];
    }

    
    public static function getNames()
    {
        return User::pluck( 'name','id');
    }

}
