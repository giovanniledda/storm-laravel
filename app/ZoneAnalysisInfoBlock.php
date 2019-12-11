<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;

class ZoneAnalysisInfoBlock extends Model
{
    use DocumentableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'zone_analysis_info_blocks';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The zone which analysis block is referred
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function zone()
    {
        return $this->hasOne('App\Zone', 'zone_analysis_info_block_id');
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
            'description' => $faker->sentence(30),
            'percentage_in_work' => $faker->numberBetween(0, 100),
        ];
    }

    /**
     *
     * Creates a Zone Analysis IB using some fake data and some others that have sense
     * @param Faker $faker
     * @return ZoneAnalysisInfoBlock
     */
    public static function createSemiFake(Faker $faker)
    {
        $data = self::getSemiFakeData($faker);
        $t = new ZoneAnalysisInfoBlock($data);
        $t->save();
        return $t;
    }

    // TODO: functions to manage 1..N Photos
}
