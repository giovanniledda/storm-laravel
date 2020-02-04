<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use function method_exists;

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
        'data_attributes' => 'array'
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
     * @return mixed|null
     */
    public function report_obj()
    {
        if ($this->report_type && $this->report_id) {
            $model = new $this->report_type;
            return $model->find($this->report_id);
        }
        return null;
    }

    /**
     * @param ApplicationLog $application_log
     * @param int|null $author_id
     */
    public static function touchForNewApplicationLog(ApplicationLog &$application_log, int $author_id = null)
    {
        self::create([
            'report_type' => ApplicationLog::class,
            'report_name' => $application_log->name,
            'report_create_date' => $application_log->created_at,
            'report_update_date' => $application_log->updated_at,
            'report_id' => $application_log->id,
            'author_id' => $author_id ? $author_id : $application_log->author_id,
            'data_attributes' => $application_log->myAttributesForReportItem(),
            'project_id' => $application_log->project_id,
        ]);
    }

    /**
     * @return mixed
     */
    public function getDataAttributes()
    {
        $obj = $this->report_obj();
        if ($obj && method_exists($obj, 'myAttributesForReportItem')) {
            return $obj->myAttributesForReportItem();
        }
    }
}
