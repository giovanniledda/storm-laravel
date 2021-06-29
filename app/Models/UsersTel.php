<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersTel extends Model
{
    protected $table = 'users_tel';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'user_id', 'phone_type', 'phone_number', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
