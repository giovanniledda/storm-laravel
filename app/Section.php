<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;
use Net7\Documents\DocumentableTrait;

class Section extends Model
{


    use DocumentableTrait;

    protected $table = 'sections';

    protected $fillable = [
      'name', 'section_type', 'position', 'code', 'boat_id'
    ];

    public function getMediaPath($media){

        $document = $media->model;
        $media_id = $media->id;
        $boat_id = $this->id;

        $section_id = $this->id;
        $boat = $this->boat;
        $boat_id = $boat->id;
        $path = 'boats' . DIRECTORY_SEPARATOR . $boat_id . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR . $section_id .
                    DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;

    }

    public function boat()
    {
        return $this->belongsTo('App\Boat');
    }

    public function subsections()
    {
        return $this->hasMany('App\Subsection');
    }

//    public function tasks()
//    {
//        return $this->hasManyThrough('App\Task', 'App\Subsection');
//    }

    public function tasks()
    {
        return $this->hasMany('App\Task');
    }

    public function map_image()
    {
        return $this->morphOne('Net7\Documents\Document', 'documentable');
    }



    public function generic_documents(){
        return $this->documents()->where('type', \Net7\Documents\Document::GENERIC_DOCUMENT_TYPE);
    }

    public function generic_images(){
        return $this->documents()->where('type', \Net7\Documents\Document::GENERIC_IMAGE_TYPE);
    }

    /**
     * Creates a Section using some fake data and some others that have sense
     *
     * @param Faker $faker
     * @param Boat $boat
     *
     * @return Section $section
     */
    public static function createSemiFake(Faker $faker, Boat $boat = null)
    {
        $section = new Section([
                'name' => $faker->numerify('Deck #'),
                'section_type' => $faker->randomElement([SECTION_TYPE_LEFT_SIDE, SECTION_TYPE_RIGHT_SIDE, SECTION_TYPE_DECK]),
                'position' => $faker->randomDigitNotNull(),
                'code' => $faker->lexify('???-???'),
                'boat_id' => $boat ? $boat->id : null
            ]
        );
        $section->save();
        return $section;
    }
}
