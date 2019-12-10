<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductUse extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_uses';

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
    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }
}
