<?php

namespace App;

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
    public function zone_analysis_info_blocks()
    {
        return $this->hasMany('App\ZoneAnalysisInfoBlock', 'application_log_section_id');
    }

    /**
     * Get the product uses info blocks for the app log section
     */
    public function product_uses_info_blocks()
    {
        return $this->hasMany('App\ProductUseInfoBlock', 'application_log_section_id');
    }

    /**
     * Get the generic info blocks for the app log section
     */
    public function generic_info_blocks()
    {
        return $this->hasMany('App\GenericDataInfoBlock', 'application_log_section_id');
    }


}
