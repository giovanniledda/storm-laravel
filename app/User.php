<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles;

    /** @optional */
    protected $ownAttributes = [
        'id',
        'name',
        'email',
        'password',
        'entity_type',
        'entity_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function projects()
    {
        return $this->belongsToMany('App\Project')
            ->using('App\ProjectUser')
            ->withPivot([
                'role',
                'created_by',
                'updated_by'
            ]);
    }

    public function boats()
    {
        return $this->belongsToMany('App\Boat')
            ->using('App\BoatUser')
            ->withPivot([
                'role',
                'created_by',
                'updated_by'
            ]);
    }

    public function setPasswordAttribute($password)
    {
//        $this->attributes['password'] = bcrypt($password);
        $this->attributes['password'] = Hash::make($password);
    }

    public function createAndGetToken()
    {
        return $this->createToken(\Config::get('auth.token_clients.personal.name'))->accessToken;
    }

    public static function onlyOne()
    {
        return (User::all()->count() == 1);
    }


}
