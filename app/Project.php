<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
//use Venturecraft\Revisionable\RevisionableTrait;
use Log;

class Project extends Model
{
    use HasStatuses;


    protected $table = 'projects';

    protected $fillable = [
       'name', 'project_status', 'boat_id', 'project_type', 'project_progress'
    ];

    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function site()
    {
        return $this->belongsTo('App\Site');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function history()
    {
        return $this->morphMany('App\History', 'historyable');
    }

     public function sections()
    {
         return $this->belongsToMany('App\Section')
            ->using('App\ProjectSections');
            /*->withPivot([
                // 'role',
                'profession_id',
                'created_at',
                'updated_at'
            ]);*/
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }


    public function users()
    {
        return $this->belongsToMany('App\User')
            ->using('App\ProjectUser')
            ->withPivot([
                // 'role',
                'profession_id',
                'created_at',
                'updated_at'
            ]);
    }


    public function generic_documents(){
        return $this->documents()->where('type', Document::GENERIC_DOCUMENT_TYPE);
    }

    public function addDocumentWithType(\App\Document $doc, $type){
        if ($type){
            $doc->type = $type;
        } else {
            $doc->type = \App\Document::GENERIC_DOCUMENT_TYPE;
        }
        $this->documents()->save($doc);

    }

}
