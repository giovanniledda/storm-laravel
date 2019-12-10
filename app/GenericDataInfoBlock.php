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

    // TODO: functions to manage 1..N Photos and 1...N Files
}
