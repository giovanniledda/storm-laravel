<?php

namespace App;

use App\Traits\JsonAPIPhotos;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;
use const DIRECTORY_SEPARATOR;

class DetectionsInfoBlock extends Model
{
    use DocumentableTrait, JsonAPIPhotos;

    protected $_photo_documents_size = ''; // 'thumb'; TODO: a regime mettere thumb (in locale va solo se si azionano le code)

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detections_info_blocks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'detections',
        'short_description',
        'application_log_section_id',
        'tool_id',
    ];

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

    public function getMediaPath($media)
    {
        $media_id = $media->id;
        /** @var ApplicationLogSection $app_log_section */
        $app_log_section = $this->application_log_section;
        if ($app_log_section) {
            /** @var ApplicationLog $app_log */
            $app_log = $app_log_section->application_log;
            if ($app_log) {
                /** @var Project $project */
                $project = $app_log->project;

                return DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $project->id .
                    DIRECTORY_SEPARATOR . 'applications_logs' . DIRECTORY_SEPARATOR  . $app_log->application_type . DIRECTORY_SEPARATOR  . $app_log->id .
                    DIRECTORY_SEPARATOR . 'applications_log_sections' . DIRECTORY_SEPARATOR  . $app_log_section->section_type . DIRECTORY_SEPARATOR  . $app_log_section->id .
                    DIRECTORY_SEPARATOR . 'detections_info_blocks' . DIRECTORY_SEPARATOR  . $this->id .
                    DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;
            }
        }

        return '/tmp/';
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
        $data['attributes']['tool'] = $this->tool;
        $data['attributes']['photos'] = $this->getPhotosApi();

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
