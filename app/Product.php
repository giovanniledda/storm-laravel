<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;

class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'p_type' => PRODUCT_TYPE_PAINTING,
    ];

    /**
     * Returns an array of data with values for each field
     *
     * @param Faker $faker
     * @return array
     */
    public static function getSemiFakeData(Faker $faker)
    {

        $data = [
            'name' => $faker->word,
            'producer' => $faker->company,
            'sv_percentage' => $faker->randomFloat(3),
            'components' => $faker->words(3),
        ];

        return $data;
    }
}
