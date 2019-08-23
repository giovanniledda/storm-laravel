<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * @param Site $site
     *
     * @return Boat $boat
     */
    public static function createSemiFake(Faker $faker, Site $site = null)
    {
        $boat = new Boat([
                'name' => $faker->suffix.' '.$faker->name,
                'registration_number' => $faker->randomDigitNotNull,
                'site_id' => $site ? $site->id : null,
                'length' => $faker->randomFloat(4, 30, 150),
                'beam' => $faker->randomFloat(4, 1, 10),
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
