<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;

class DetectionsInfoBlock extends Model
{
    use DocumentableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detections_info_blocks';

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
        'detections' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tool()
    {
        return $this->belongsTo('App\Tool', 'tool_id');
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
            'detections' => $faker->words(30),
            'short_description' => $faker->sentence(15)
        ];
    }

    /**
     *
     * Creates a Generic Data IB using some fake data and some others that have sense
     * @param Faker $faker
     * @return DetectionsInfoBlock
     */
    public static function createSemiFake(Faker $faker)
    {
        $t = new DetectionsInfoBlock(self::getSemiFakeData($faker));
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

    // TODO: functions to manage 1..N Photos and 1...N Files
}
