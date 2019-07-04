<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';

    public function statusable()
    {
        return $this->morphTo();
    }
}
