<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;

class ProductUseInfoBlock extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_use_info_blocks';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     * See: https://laravel.com/docs/5.8/eloquent-mutators#array-and-json-casting
     *
     * @var array
     */
    protected $casts = [
        'components' => 'array',
        'thinners' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    /**
     * The application log section
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application_log_section()
    {
        return $this->belongsTo('App\ApplicationLogSection', 'application_log_section_id');
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
            'name' => $faker->word,
            'viscosity' => $faker->randomFloat(2),
            'components' => $faker->words(30),
            'thinners' => $faker->words(30),
        ];
    }

    /**
     *
     * Creates a Product Use IB using some fake data and some others that have sense
     * @param Faker $faker
     * @return ProductUseInfoBlock
     */
    public static function createSemiFake(Faker $faker)
    {
        $data = self::getSemiFakeData($faker);
        $t = new ProductUseInfoBlock($data);
        $t->save();
        return $t;
    }

    /**
     * @return array
     */
    public function toJsonApi()
    {
        $data = [
            'type' => $this->table,
            'id' => $this->id,
            'attributes' => parent::toArray()
        ];
        return $data;
    }

    /**
     * Overrides parent function
     * @return array|string
     */
    public function toArray()
    {
        return $this->toJsonApi();
    }
}
