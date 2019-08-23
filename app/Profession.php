<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;


class Profession extends Model
{
    protected $table = 'professions';

    protected $fillable = [
       'name',
       'is_storm',
    ];


    /**
     * Creates a Section using some fake data and some others that have sense
     *
     * @param Faker $faker
     *
     * @return Profession $profession
     */
    public static function createSemiFake(Faker $faker)
    {
        $profession = new Profession([
                'name' => $faker->jobTitle,
            ]
        );
        $profession->save();
        return $profession;
    }
}