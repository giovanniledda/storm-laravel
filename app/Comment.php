<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'body',
        'author_id',
        'commentable_type',  // ex: App\Task
        'commentable_id',    // ex: 1
    ];

    public function commentable()
    {
        return $this->morphTo();
    }
}
