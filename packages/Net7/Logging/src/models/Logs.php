<?php

namespace Net7\Logging\models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use SoftDeletes;

    protected $table = 'logs';
    
    protected $fillable = [
        'env',
        'message',
        'level',
        'context',
        'extra'
    ];

    protected $casts = [
        'context' => 'array',
        'extra'   => 'array'
    ];
}
