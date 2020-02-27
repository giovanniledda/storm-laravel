<?php

namespace App;

use App\Observers\ProjectObserver;
use App\Observers\ZoneObserver;
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
     * boot function
     */
    protected static function boot()
    {
        parent::boot();
        Zone::observe(ZoneObserver::class);
    }

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
     * The project wich the zone belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('App\Project', 'project_id');
    }

    /**
     * Restricted versione of the project wich the zone belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project_for_api()
    {
        return $this->project()->select(['id', 'name', 'acronym', 'project_type', 'project_status']);
    }

    /**
     * The zone analysis info blocks which the zone belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zone_analysis_info_blocks()
    {
        return $this->hasMany('App\ZoneAnalysisInfoBlock', 'zone_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany('App\Task', 'zone_id');
    }

    /**
     * Returns an array of data with values for each field
     *
     * @param Faker $faker
     * @param int $project_id
     * @return array
     */
    public static function getSemiFakeData(Faker $faker, int $project_id = null)
    {
        return [
            'code' => $faker->regexify('[A-Z|0-9]{3}'),
            'description' => $faker->sentence(3),
            'extension' => $faker->randomFloat(2, 1, 80),
            'project_id' => $project_id,
        ];
    }

    /**
     *
     * Creates a Zone using some fake data and some others that have sense
     * @param Faker $faker
     * @param int|null $project_id
     * @return Zone
     */
    public static function createSemiFake(Faker $faker, int $project_id = null)
    {
        $data = self::getSemiFakeData($faker, $project_id);
        $t = new Zone($data);
        $t->save();
        return $t;
    }

    /**
     * Just an alias for the count
     * @param array $data (key - values)
     * @return int
     */
    public static function countWithAttributesAndValues($data = [])
    {
        return self::where($data)->count();
    }
}
