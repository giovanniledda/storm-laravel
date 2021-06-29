<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tools';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The detections info block which the tool belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detections_info_blocks()
    {
        return $this->hasMany('App\DetectionsInfoBlock', 'tool_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany('App\Project', 'project_tool');
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
            'producer' => $faker->company,
            'serial_number' => $faker->regexify('[a-z|0-9]{8}'),
            'calibration_expiration_date' => $faker->dateTimeThisDecade(),
        ];
    }

    /**
     * Creates a Product using some fake data and some others that have sense
     * @param Faker $faker
     * @return Tool
     */
    public static function createSemiFake(Faker $faker)
    {
        $t = new self(self::getSemiFakeData($faker));
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
            'attributes' => parent::toArray(),
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
