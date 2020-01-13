<?php

namespace App;

use App\Traits\JsonAPIPhotos;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;

class ZoneAnalysisInfoBlock extends Model
{
    use DocumentableTrait, JsonAPIPhotos;

    protected $_photo_documents_size = ''; // 'thumb'; TODO: a regime mettere thumb (in locale va solo se si azionano le code)

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone()
    {
        return $this->belongsTo('App\Zone', 'zone_id');
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

    // TODO: functions to manage 1..N Photos
}
