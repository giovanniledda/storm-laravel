<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Get the zone analysis info blocks for the app log section
     */
    public function application_log_sections()
    {
        return $this->hasMany('App\ApplicationLogSection', 'application_log_id');
    }

}
