<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Net7\Documents\DocumentableTrait;

class ZoneAnalysisInfoBlock extends Model
{
    use DocumentableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'zone_analysis_info_blocks';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The parent zone for the "leaf" zones
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone()
    {
        return $this->belongsTo('App\Zone', 'zone_id');
    }

    // TODO: functions to manage 1..N Photos
}
