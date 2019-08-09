<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{

    protected $table = 'sections';

    protected $fillable = [
      'name', 'section_type', 'position', 'code', 'boat_id'
    ];

    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function subsections()
    {
        return $this->hasMany('App\Subsection');
    }

    public function tasks()
    {
        return $this->hasManyThrough('App\Task', 'App\Subsection');
    }

    public function map_image()
    {
        return $this->morphOne('App\Document', 'documentable');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }


    public function generic_documents(){
        return $this->documents()->where('type', \App\Document::GENERIC_DOCUMENT_TYPE);
    }

    public function generic_images(){
        return $this->documents()->where('type', \App\Document::GENERIC_IMAGE_TYPE);
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
