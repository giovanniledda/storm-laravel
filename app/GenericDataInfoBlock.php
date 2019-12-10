<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;

class GenericDataInfoBlock extends Model
{
    use DocumentableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'generic_data_info_blocks';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The application log section
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application_log_section()
    {
        return $this->belongsTo('App\ApplicationLogSection', 'application_log_section_id');
    }

    // TODO: functions to manage 1..N Photos and 1...N Files
}
