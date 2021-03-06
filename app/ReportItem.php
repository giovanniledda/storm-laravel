<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Net7\Documents\Document;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use function get_class;
use function method_exists;
use function property_exists;
use const MEASUREMENT_FILE_TYPE;
use const REPORT_CORROSION_MAP_SUBTYPE;
use const REPORT_ITEM_TYPE_APPLICATION_LOG;
use const REPORT_ITEM_TYPE_CORR_MAP_DOC;
use const REPORT_ITEM_TYPE_CORR_MAP_OV_DOC;
use const REPORT_ITEM_TYPE_ENVIRONM_LOG;

class ReportItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'report_items';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    // report_type
    // report_name
    // data_attributes
    // report_links
    // report_create_date
    // report_update_date
    // report_id
    // author_id
    // project_id
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     * See: https://laravel.com/docs/5.8/eloquent-mutators#array-and-json-casting
     *
     * @var array
     */
    protected $casts = [
        'data_attributes' => 'array',
        'report_links' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reportable()
    {
        return $this->morphTo();
    }

    /**
     * @param $document_id
     * @return ReportItem
     */
    public static function findByDocumentId($document_id)
    {
        return self::where('reportable_type', '=', Document::class)
            ->where('reportable_id', '=', $document_id)
            ->first();
    }

    /**
     * @param $template
     * @return string
     */
    public static function getTypeByTemplate($template)
    {
       return ($template == REPORT_CORROSION_MAP_SUBTYPE) ? REPORT_ITEM_TYPE_CORR_MAP_DOC : REPORT_ITEM_TYPE_CORR_MAP_OV_DOC;
    }

    /**
     * @param ApplicationLog $application_log
     * @param int|null $author_id
     */
    public static function touchForNewApplicationLog(ApplicationLog &$application_log, int $author_id = null)
    {
        $report_item = self::create([
            'report_type' => REPORT_ITEM_TYPE_APPLICATION_LOG,
            'report_id' => $application_log->id,
            'report_name' => $application_log->name,
            'report_create_date' => $application_log->created_at,
            'report_update_date' => $application_log->updated_at,
            'author_id' => $author_id ? $author_id : $application_log->author_id,
            'data_attributes' => $application_log->myAttributesForReportItem(),
            'project_id' => $application_log->project_id,
            'reportable_type' => ApplicationLog::class,
            'reportable_id' => $application_log->id,
        ]);

    }

    /**
     * @param Document $env_log_document
     * @param int|null $author_id
     * @param int|null $project_id
     * @param array|null $data_attributes
     */
    public static function touchForNewEnvironmentalLog(Document &$env_log_document, int $author_id = null, int $project_id = null, array $data_attributes = null)
    {
        self::create([
            'report_type' => REPORT_ITEM_TYPE_ENVIRONM_LOG,
            'report_id' => $env_log_document->id,
            'report_name' => $env_log_document->title,
            'report_create_date' => $env_log_document->created_at,
            'report_update_date' => $env_log_document->updated_at,
            'author_id' => $author_id ? $author_id : $env_log_document->author_id,
            'data_attributes' => $data_attributes,
            'report_links' => self::getGdriveInfoForDocument($env_log_document),
            'project_id' => $project_id,
            'reportable_type' => Document::class,
            'reportable_id' => $env_log_document->id,
        ]);
    }

    /**
     * @param Document|null $document
     * @param string $type
     * @param int|null $author_id
     * @param int|null $project_id
     * @param array|null $data_attributes
     * @return mixed
     */
    public static function touchForNewDocument(Document &$document = null, string $type = '', int $author_id = null, int $project_id = null, array $data_attributes = null)
    {
        return self::create([
            'report_type' => $type,
            'report_id' => $document ? $document->id : null,
            'report_name' => $document ? $document->title : 'Report document in progress...',
            'report_create_date' => $document ? $document->created_at : null,
            'report_update_date' => $document ? $document->updated_at : null,
            'author_id' => $author_id ? $author_id : $document->author_id,
            'data_attributes' => $data_attributes,
            'report_links' => $document ? self::getGdriveInfoForDocument($document) : null,
            'project_id' => $project_id,
            'reportable_type' => Document::class,
            'reportable_id' => $document ? $document->id : null,
        ]);
    }

    /**
     * @param Document $document
     * @param string $type
     * @param int|null $author_id
     * @param int|null $project_id
     * @param array|null $data_attributes
     */
    public function updateForDocument(Document &$document, string $type, int $author_id = null, int $project_id = null, array $data_attributes = null)
    {
        $this->update([
            'report_type' => $type,
            'report_id' => $document->id,
            'report_name' => $document->title,
            'report_create_date' => $document->created_at,
            'report_update_date' => $document->updated_at,
            'author_id' => $author_id ? $author_id : $document->author_id,
            'data_attributes' => $data_attributes,
            'report_links' => self::getGdriveInfoForDocument($document),
            'project_id' => $project_id,
            'reportable_type' => Document::class,
            'reportable_id' => $document->id,
        ]);
    }

    /**
     * @param Document|null $document
     * @return array
     */
    protected static function getGdriveInfoForDocument(Document $document = null)
    {
        return $document ? [
            'gdrive_url' => $document->getGDriveLink(),
            'gdrive_filename' => $document->getGDriveFilename()
        ] : [];
    }

    /**
     * Real time report links updated
     * @return array
     */
    public function getReportLinks()
    {
        $object = $this->reportable;
        if ($object && get_class($object) == ApplicationLog::class) {
            // get-app-log-structure
            return [
//                'edit_url' => '/api/v1/projects/'.$this->project_id.'/app-log-structure/'.$object->id
                'edit_url' => url("/api/v1/projects/{$this->project_id}/app-log-structure/{$object->id}")
            ];
        }
        return self::getGdriveInfoForDocument($object);
    }

    /**
     * @return array
     */
    public function getDataAttributesForEnvironmentalLog()
    {
        $document = $this->reportable;
        $log = $this->project->measurementLogs()->where('id', '=', $document->id)->first();
        $min_date = Measurement::getMinTimeByDocument($log->id);
        $max_date = Measurement::getMaxTimeByDocument($log->id);
        $data_attributes = $this->data_attributes;

        return [
            'id' => $document->id,
            'area' => $data_attributes['area'],
            'measurement_interval_dates' => [
                'min' => $min_date,
                'max' => $max_date
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getDataAttributes()
    {
        if ($this->report_type == REPORT_ITEM_TYPE_ENVIRONM_LOG) {
            return $this->getDataAttributesForEnvironmentalLog();
        }
        $obj = $this->reportable;
        if ($obj && method_exists($obj, 'myAttributesForReportItem')) {
            return $obj->myAttributesForReportItem();
        } else {
            return $this->data_attributes;
        }
    }

    /**
     * @return string
     */
    public function getReportName()
    {
        $obj = $this->reportable;
        if ($obj) {
            return $obj->name ?? $obj->title;
        } else {
            return $this->report_name;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author_for_api()
    {
//        return $this->author()->pluck('name')->get();
        return $this->author()->select(['id', 'name', 'surname'])->without(['roles'])->first();
    }
}
