<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Lecturize\Addresses\Traits\HasAddresses;
use StormUtils;
use Faker\Generator as Faker;

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


    // public function addDocument(\Net7\Documents\Document $document, $type=false){
    //     $this->documents()->save($document);
    // }

    // public function documents()
    // {
    //     return $this->morphMany('Net7\Documents\Document', 'documentable');
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


    /**
     * Creates a Site using some fake data and some others that have sense
     *
     * @param Faker $faker
     * @param int $how_many_addresses
     *
     * @return Site $site
     */
    public static function createSemiFake(Faker $faker, $how_many_addresses = 0)
    {
        $site = new Site([
            'name' => $faker->company,
            'location' => $faker->address,
            'lat' => $faker->latitude,
            'lng' => $faker->longitude,
        ]);
        $site->save();

        if ($how_many_addresses > 0) {
            for ($i = 0; $i < $how_many_addresses; $i++) {
                $site->addAddress([
                    'street'     => $faker->streetAddress,
                    'city'       => $faker->city,
                    'post_code'  => $faker->postcode,
                    'country'    => $faker->countryCode, // ISO-3166-2 or ISO-3166-3 country code
                ]);
            }
        }
        return $site;
    }
}
