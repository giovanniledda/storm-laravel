<?php

namespace App;

use App\Notifications\StormResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use const PERMISSION_ADMIN;
use function snake_case;
use Spatie\Permission\Traits\HasRoles;
use Lecturize\Addresses\Traits\HasAddresses;


class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, CanResetPassword, HasAddresses;

    protected $fillable = [
        'name', 'surname', 'email', 'password', 'is_storm'
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
     * See: https://laraveldaily.com/why-use-appends-with-accessors-in-eloquent/
     *
     * @var array
     */
    protected $appends = ['is_storm_user', 'is_admin', 'can_login'];

    /**
     * The custom method for is_storm_user (not existent on DB) attribute
     * See: https://laraveldaily.com/why-use-appends-with-accessors-in-eloquent/
     *
     */
    public function getIsStormUserAttribute() {
        return $this->is_storm;
    }

    /**
     * The custom method for is_admin (not existent on DB) attribute
     * See: https://laraveldaily.com/why-use-appends-with-accessors-in-eloquent/
     *
     */
    public function getIsAdminAttribute() {
        return $this->can(PERMISSION_ADMIN);
    }

    /**
     * The custom method for can_login (not existent on DB) attribute
     * See: https://laraveldaily.com/why-use-appends-with-accessors-in-eloquent/
     *
     */
    public function getCanLoginAttribute() {
        return !$this->disable_login;
    }

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

    public function countProjects()
    {
        return $this->projects()->count();
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

    public function phones()
    {
        return $this->hasMany('App\UsersTel');
    }

    public function countPhones()
    {
        return $this->phones()->count();
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
