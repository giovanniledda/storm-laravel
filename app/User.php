<?php

namespace App;

use App\Notifications\StormResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use function snake_case;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, CanResetPassword;

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

    public static function onlyOne()
    {
        return (User::all()->count() == 1);
    }

    public function projects()
    {
        return $this->belongsToMany('App\Project')
            ->using('App\ProjectUser')
            ->withPivot([
                'profession_id',
                'created_by',
                'updated_by'
            ]);
    }

    public function boats()
    {
        return $this->belongsToMany('App\Boat')
            ->using('App\BoatUser')
            ->withPivot([
                'profession_id',
                'created_by',
                'updated_by'
            ]);
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
//        $this->attributes['password'] = Hash::make($password);
    }

    public function createAndGetToken()
    {
        return $this->createToken(\Config::get('auth.token_clients.personal.name'))->accessToken;
    }

    public function getNickname()
    {
        // per ora la logica è questa ma possiamo inserire anche un nuovo campo
        return snake_case($this->name);
    }

    /**
     * OVERRIDES TRAIT (CanResetPassword) FUNCTION
     *
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new StormResetPasswordNotification($token));
    }

}
