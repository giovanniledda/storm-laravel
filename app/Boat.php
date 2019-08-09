<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{

    protected $table = 'boats';
    protected $fillable = [
        'name',
        'registration_number',
        'site_id',
        'flag',
        'manufacture_year',
        'length',
        'draft',
        'beam'

    ];

    public function site()
    {
        return $this->hasOne('App\Site');
//        return $this->hasOneThrough('App\Site', 'App\Project');  // NON funziona perché i progetti sono "many" e il site è "one"
    }

    public function sections()
    {
        return $this->hasMany('App\Section');
    }

    public function subsections()
    {
        return $this->hasManyThrough('App\Subsection', 'App\Section');
    }

    public function projects()
    {
        return $this->hasMany('App\Project');
    }

    public function documents()
    {
        return $this->morphMany('App\Document', 'documentable');
    }

    public function history()
    {
        return $this->morphMany('App\History', 'historyable');
    }

    public function associatedUsers() {
        return $this->hasMany('App\BoatUser');
    }


    public function detailed_images(){
        return $this->documents()->where('type', \App\Document::DETAILED_IMAGE_TYPE);
    }

    public function additional_images(){
        return $this->documents()->where('type', \App\Document::ADDITIONAL_IMAGE_TYPE);
    }

    public function generic_images(){
        return $this->documents()->where('type', \App\Document::GENERIC_IMAGE_TYPE);
    }

    public function generic_documents(){
        return $this->documents()->where('type', \App\Document::GENERIC_DOCUMENT_TYPE);
    }

    public function addDocumentWithType(\App\Document $doc, $type){
        if ($type){
            $doc->type = $type;
        } else {
            $doc->type = \App\Document::GENERIC_DOCUMENT_TYPE;
        }
        $this->documents()->save($doc);

    }


    // owner ed equipaggio
    public function users()
    {

        return $this->belongsToMany('App\User')
            ->using('App\BoatUser')
            ->withPivot([
                'profession_id',
                'created_by',
                'updated_by'
            ]);
    }
}
