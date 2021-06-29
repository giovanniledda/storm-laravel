<?php

namespace App;

use App\Observers\CommentObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'body',
        'author_id',
        'commentable_type',  // ex: App\Task
        'commentable_id',    // ex: 1
    ];

    protected static function boot()
    {
        parent::boot();

        self::observe(CommentObserver::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function authorNickname()
    {
        // auhtor è un App\User
        return $this->author ? $this->author->getNickname() : '-';
    }
}
