<?php

namespace App;

use App\Observers\ApplicationLogObserver;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use function env;
use const APPLICATION_LOG_SECTION_TYPE_ZONES;
use const APPLICATION_TYPE_COATING;
use const APPLICATION_TYPE_FILLER;
use const APPLICATION_TYPE_HIGHBUILD;
use const APPLICATION_TYPE_PRIMER;
use const APPLICATION_TYPE_UNDERCOAT;

class ApplicationLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        ApplicationLog::observe(ApplicationLogObserver::class);
    }

    /**
     * Get the zone analysis info blocks for the app log section
     */
    public function application_log_sections()
    {
        return $this->hasMany('App\ApplicationLogSection', 'application_log_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getStartedSectionsQuery()
    {
        return $this->application_log_sections()->where('is_started', '=', 1);
    }

    /**
     * @return mixed
     */
    public function getStartedSections()
    {
        return $this->getStartedSectionsQuery()->get();
    }

    /**
     * @return mixed
     */
    public function countStartedSections()
    {
        return $this->getStartedSectionsQuery()->count();
    }

    /**
     * @param $type
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getSectionsByTypeQuery($type)
    {
        return $this->application_log_sections()->where('section_type', '=', $type);
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getZonesSection()
    {
        return $this->getSectionsByTypeQuery(APPLICATION_LOG_SECTION_TYPE_ZONES)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    /**
     * Gives the project's boat
     *
     * @return Boat|null
     */
    public function boat()
    {
        return $this->project ? $this->project->boat : null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|object|null
     */
    public function author_for_api()
    {
        return $this->author()->select(['name', 'surname'])->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function last_editor()
    {
        return $this->belongsTo('App\User', 'last_editor_id');
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|object|null
     */
    public function last_editor_for_api()
    {
        return $this->last_editor()->select(['name', 'surname'])->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function report_item()
    {
        return $this->morphOne('App\ReportItem', 'reportable');
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
            'application_type' => $faker->randomElement([
                APPLICATION_TYPE_PRIMER,
                APPLICATION_TYPE_FILLER,
                APPLICATION_TYPE_HIGHBUILD,
                APPLICATION_TYPE_UNDERCOAT,
                APPLICATION_TYPE_COATING
            ])
        ];
    }

    /**
     *
     * Creates a Application Log using some fake data and some others that have sense
     * @param Faker $faker
     * @return ApplicationLog
     */
    public static function createSemiFake(Faker $faker)
    {
        $data = self::getSemiFakeData($faker);
        $t = new ApplicationLog($data);
        $t->save();
        return $t;
    }


    /**
     * An internal ID calculated on a "per-boat" base
     * @param $boat_id
     * @return integer
     */
    public static function getLastInternalProgressiveIDByBoat($boat_id)
    {
        $max = ApplicationLog::join('projects', 'projects.id', '=', 'application_logs.project_id')
            ->where('projects.boat_id', '=', $boat_id)
            ->max('application_logs.internal_progressive_number');
        return $max ? $max : 0;
    }

    /**
     * Goives total number of tasks calculated on a "per-boat" base
     * @param $boat_id
     * @return integer
     */
    public static function countApplicationLogsByBoat($boat_id)
    {
        return ApplicationLog::join('projects', 'projects.id', '=', 'application_logs.project_id')
            ->where('projects.boat_id', '=', $boat_id)
            ->count();
    }

    /**
     * @return void
     */
    public function updateInternalProgressiveNumber()
    {
        if (env('INTERNAL_PROG_NUM_ACTIVE')) {
            $p_boat = $this->boat();
            if ($p_boat) {
                $highest_internal_pn = ApplicationLog::getLastInternalProgressiveIDByBoat($p_boat->id);
                $this->update(['internal_progressive_number' => ++$highest_internal_pn]);
            }
        }
    }

    /**
     * Convert object to an object JSON API compliant
     * @return array
     */
    public function toJsonApi()
    {
        $this->application_log_sections;
        $data = [
            'type' => $this->table,
            'id' => $this->id,
            'attributes' => $this
        ];
        // editor e author vanno aggiunti dopo aver assegnato $this
        $data['attributes']['author'] = $this->author_for_api();
        $data['attributes']['last_editor'] = $this->last_editor_for_api();
        return $data;
    }

    /**
     * @return array
     */
    public function getUsedZones()
    {
        $used_zones = [];
        /** @var ApplicationLogSection $zones_section */
        $zones_section = $this->getZonesSection();
        if ($zones_section && $zones_section->is_started) {
            if ($zones_section->zone_analysis_info_blocks()->with('zone')->count()) {
                $zones_ib = $zones_section->zone_analysis_info_blocks()->with('zone')->get();
                foreach ($zones_ib as $zone_ib) {
//                    $used_zones[] = $zone_ib->zone()->pluck('code');
                    $used_zones[] = $zone_ib->zone()->select(['id', 'code', 'description'])->first();
                }
            }
        }
        return $used_zones;
    }

    /**
     * Array of attributes made ad hoc for ReportItem "data_attributes" field
     * @return array
     */
    public function myAttributesForReportItem()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'application_type' => $this->application_type,
            'last_editor' => $this->last_editor_for_api(),
            'started_sections' => $this->getStartedSections()->pluck('section_type'),
            'zones' => $this->getUsedZones()
        ];
    }
}
