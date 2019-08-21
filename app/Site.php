<?php

namespace App;

use App\Observers\SiteObserver;
use Illuminate\Database\Eloquent\Model;
use Lecturize\Addresses\Traits\HasAddresses;
use StormUtils;

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

    protected static function boot()
    {
        parent::boot();

//        Site::observe(SiteObserver::class);
    }

    public function projects()
    {
        return $this->hasMany('App\Project');
    }

    public function boats()
    {
        return $this->hasMany('App\Boat');
    }


    // public function addDocument(\App\Document $document, $type=false){
    //     $this->documents()->save($document);
    // }

    // public function documents()
    // {
    //     return $this->morphMany('App\Document', 'documentable');
    // }

    public function getAddresses($pagination = false)
    {
        if ($this->hasAddress()) {
            return $pagination ? $this->addresses()->paginate(StormUtils::getItemsPerPage()) : $this->addresses()->get();
        }
        return [];
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
