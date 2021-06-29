<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;

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
    public static function createSemiFake(Faker $faker, $slug = 'worker')
    {
        $is_storm = ($slug === 'worker') ? $faker->randomFloat(0, 0, 1) : 0;
        $profession = new self([
                'name' => ($slug === 'worker') ? $faker->jobTitle : 'Owner',
                'is_storm' => $is_storm,
                'slug'=>$slug,
            ]
        );
        $profession->save();

        return $profession;
    }
}
