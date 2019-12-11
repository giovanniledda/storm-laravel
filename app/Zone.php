<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'zones';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The parent zone for the "leaf" zones
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent_zone()
    {
        return $this->belongsTo('App\Zone', 'parent_zone_id');
    }

    /**
     * The children zones for the "parent" zone
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children_zones()
    {
        return $this->hasMany('App\Zone', 'parent_zone_id');
    }

    /**
     * The zone analysis info block which the zone belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone_analysis_info_block()
    {
        return $this->belongsTo('App\ZoneAnalysisInfoBlock', 'zone_analysis_info_block_id');
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
            'code' => $faker->regexify('[A-Z|0-9]{3}'),
            'description' => $faker->sentence(3),
            'extension' => $faker->randomFloat(2),
        ];
    }

    /**
     *
     * Creates a Zone using some fake data and some others that have sense
     * @param Faker $faker
     * @return Zone
     */
    public static function createSemiFake(Faker $faker)
    {
        $data = self::getSemiFakeData($faker);
        $t = new Zone($data);
        $t->save();
        return $t;
    }
}
