<?php

namespace App;

use const APPLICATION_LOG_SECTION_TYPE_APPLICATION;
use const APPLICATION_LOG_SECTION_TYPE_INSPECTION;
use const APPLICATION_LOG_SECTION_TYPE_PREPARATION;
use const APPLICATION_LOG_SECTION_TYPE_ZONES;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;

class ApplicationLogSection extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_log_sections';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the zone analysis info blocks for the app log section
     */
    public function application_log()
    {
        return $this->belongsTo('App\ApplicationLog');
    }

    /**
     * Get the zone analysis info blocks for the app log section
     */
    public function zone_analysis_info_blocks()
    {
        return $this->hasMany('App\ZoneAnalysisInfoBlock', 'application_log_section_id');
    }

    /**
     * Get the product use info blocks for the app log section
     */
    public function product_use_info_blocks()
    {
        return $this->hasMany('App\ProductUseInfoBlock', 'application_log_section_id');
    }

    /**
     * Get the generic info blocks for the app log section
     */
    public function generic_data_info_blocks()
    {
        return $this->hasMany('App\GenericDataInfoBlock', 'application_log_section_id');
    }

    /**
     * Get the detections info blocks for the app log section
     */
    public function detections_info_blocks()
    {
        return $this->hasMany('App\DetectionsInfoBlock', 'application_log_section_id');
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getSurfaceInspectionDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Surface inspection')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getSaltDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Salt - Bresle test')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getTemperatureAndHumidityDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Temperature & Humidity')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getAdhesionDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Adhesion')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getFairnessDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Fairness')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getHardnessDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Hardness')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getThicknessDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Thickness')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getGlassDoiHazeRspecDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Gloss / DOI / Haze / Rspec')->first();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getOrangePeelDetectionBlock()
    {
        return $this->detections_info_blocks()->where('name', '=', 'Orange peel')->first();
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
            'section_type' => $faker->randomElement([
                APPLICATION_LOG_SECTION_TYPE_ZONES,
                APPLICATION_LOG_SECTION_TYPE_PREPARATION,
                APPLICATION_LOG_SECTION_TYPE_APPLICATION,
                APPLICATION_LOG_SECTION_TYPE_INSPECTION,
            ]),
            'is_started' => $faker->boolean(30),
            'date_hour' => $faker->dateTime(),
        ];
    }

    /**
     * Creates a Application Log Section using some fake data and some others that have sense
     * @param Faker $faker
     * @return ApplicationLogSection
     */
    public static function createSemiFake(Faker $faker)
    {
        $data = self::getSemiFakeData($faker);
        $t = new self($data);
        $t->save();

        return $t;
    }

    /**
     * @return array
     */
    public function toJsonApi()
    {
        // chiamare questi campi/metodi così mi serve per caricarli in memoria e rendrli quindi disponibili nella parent::toArray()
        $this->zone_analysis_info_blocks;
        $this->product_use_info_blocks;
        $this->detections_info_blocks;
        $this->generic_data_info_blocks;
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
