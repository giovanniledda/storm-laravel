<?php

namespace App\Models;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Lecturize\Addresses\Traits\HasAddresses;
use StormUtils;

class Site extends Model
{
    use HasFactory;
    use HasAddresses;

    protected $table = 'sites';

    protected $fillable = [
        'name',
        'location',
        'lat',
        'lng',
    ];

    /**
     * @param mixed $name
     * @return Site
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $location
     * @return Site
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param mixed $lat
     * @return Site
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @param mixed $lng
     * @return Site
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    protected static function boot()
    {
        parent::boot();

//        Site::observe(SiteObserver::class);
    }

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class);
    }

    public function boats()
    {
        return $this->hasMany(\App\Models\Boat::class);
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
        $site = new self([
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
