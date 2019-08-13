<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Lecturize\Addresses\Traits\HasAddresses;

class Site extends Model
{
    use HasAddresses;

    protected $table = 'sites';

    protected $fillable = [
        'name',
        'location',
        'lat',
        'lng'
    ];

    public function projects()
    {
        return $this->hasMany('App\Project');
    }


    // public function addDocument(\App\Document $document, $type=false){
    //     $this->documents()->save($document);
    // }

    // public function documents()
    // {
    //     return $this->morphMany('App\Document', 'documentable');
    // }

    public function getAddresses()
    {
        return $this->hasAddress() ? $this->addresses()->get() : [];
    }

    public function getAddress($address_id)
    {
        return $this->addresses()->where('id', $address_id)->first();
    }

    public function countAddresses()
    {
        return $this->addresses()->count();
    }
}
