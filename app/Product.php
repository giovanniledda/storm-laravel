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
     * The attributes that should be cast to native types.
     * See: https://laravel.com/docs/5.8/eloquent-mutators#array-and-json-casting
     *
     * @var array
     */
    protected $casts = [
        'components' => 'array',
    ];


    /**
     * The product use info block which the product belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function product_use_info_blocks()
    {
        return $this->hasMany('App\ProductUseInfoBlock', 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany('App\Project', 'project_product');
    }

    /**
     * Returns an array of data with values for each field
     *
     * @param Faker $faker
     * @return array
     */
    public static function getSemiFakeData(Faker $faker)
    {
        return [
            'name' => $faker->colorName,
            'producer' => $faker->company,
            'p_type' => PRODUCT_TYPE_PAINTING,
            'sv_percentage' => $faker->randomFloat(3),
            'components' => [$faker->safeColorName, $faker->safeColorName, $faker->safeColorName],
        ];
    }

    /**
     *
     * Creates a Product using some fake data and some others that have sense
     * @param Faker $faker
     * @return Product
     */
    public static function createSemiFake(Faker $faker)
    {
        $p = new Product(self::getSemiFakeData($faker));
        $p->save();
        return $p;
    }

}
