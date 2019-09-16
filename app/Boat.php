<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Net7\Documents\DocumentableTrait;

class Boat extends Model
{

    use DocumentableTrait;

    protected $table = 'boats';
    protected $fillable = [
        'name',
        'registration_number',
       // 'site_id',
        'flag',
        'manufacture_year',
        'length',
        'draft',
        'beam'

    ];


    public function getMediaPath($media){

        $document = $media->model;
        $media_id = $media->id;
        $boat_id = $this->id;

        $path = 'boats' . DIRECTORY_SEPARATOR . $boat_id . DIRECTORY_SEPARATOR . $document->type .
               DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;

    }


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


    public function history()
    {
        return $this->morphMany('App\History', 'historyable');
    }

    public function associatedUsers() {
        return $this->hasMany('App\BoatUser');
    }



    // owner ed equipaggio
    public function users()
    {
        return $this->belongsToMany('App\User')
            ->using('App\BoatUser')
            ->withPivot([
                'profession_id'
            ]);
    }


    /**
     * @param int $uid
     *
     * @return BelongsToMany
     */
    public function getUserByIdBaseQuery($uid)
    {
        return $this->users()->where('users.id', '=', $uid);
    }

    /**
     * @param int $uid
     *
     * @return User
     */
    public function getUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->first();
    }

    /**
     * @param int $uid
     *
     * @return boolean
     */
    public function hasUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->count() > 0;
    }


    /**
     * Creates a Boat using some fake data and some others that have sense
     *
     * @param Faker $faker
     *
     * @return Boat $boat
     */
    public static function createSemiFake(Faker $faker)
    {
        $boat = new Boat([
                'name' => $faker->suffix.' '.$faker->name,
                'registration_number' => $faker->randomDigitNotNull,
                'length' => $faker->randomFloat(4, 30, 150),
                'beam' => $faker->randomFloat(4, 5, 22),
                'draft' => $faker->randomFloat(4, 1, 2),
                'boat_type' => $faker->randomElement([BOAT_TYPE_MOTOR, BOAT_TYPE_SAIL]),
                'flag' => $faker->country(),
                'manufacture_year' => $faker->year(),
            ]
        );
        $boat->save();
        return $boat;
    }
}
