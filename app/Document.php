<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Models\Media as BaseMedia;
use \Venturecraft\Revisionable\RevisionableTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

    // class Document extends BaseMedia 
class Document  extends Model implements HasMedia
{
    // see https://docs.spatie.be/laravel-medialibrary/v7/basic-usage/preparing-your-model
    use HasMediaTrait;

    // see https://github.com/VentureCraft/revisionable 
    
    use RevisionableTrait;

    // protected $revisionCleanup = true; //Remove old revisions (works only when used with $historyLimit)
    // protected $historyLimit = 500; //Maintain a maximum of 500 changes at any point of time, while cleaning up old revisions.
    // 

    protected $fillable = [
        'title'
    ];
}
