<?php

namespace App;

use App\Traits\JsonAPIPhotos;
use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;
use function date;
use function json_decode;
use function time;
use const DIRECTORY_SEPARATOR;

class History extends Model
{
    use DocumentableTrait, JsonAPIPhotos;

    protected $_photo_documents_size = ''; // 'thumb'; TODO: a regime mettere thumb (in locale va solo se si azionano le code)

    protected $table = 'history';

    protected $fillable = [
        'event_body',
        'event_date',
        'historyable_id',
        'historyable_type',
        'updated_at'
    ];

    public function historyable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function getFirstcomment()
    {
        return $this->comments()->oldest()->first();
    }

    public function comments_for_api()
    {
        return $this->comments()
            ->join('users', 'users.id', '=', 'comments.author_id')
            ->select(['comments.id',
                'comments.body',
                'comments.created_at',
                'comments.updated_at',
                'users.name as author_name',
                'users.surname  as author_surname']);
    }

    /**
     * Extracts the attribute value from the "event_body" json field
     *
     * @param $attribute
     * @return string|null
     */
    public function getBodyAttribute($attribute)
    {
        $event_body_arr = json_decode($this->event_body, 1);
        if (!empty($event_body_arr)) {
            return isset($event_body_arr[$attribute]) ? $event_body_arr[$attribute] : null;
        }
    }

    public function getMediaPath($media)
    {
        $document = $media->model;
        $media_id = $media->id;

        $history_id = $this->id;
        $path = 'histories' . DIRECTORY_SEPARATOR .
            $history_id . DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Updates updated_at field
     */
    public function updateLastEdit()
    {
        $this->update(['updated_at' => date('Y-m-d H:i:s', time())]);
    }
}
