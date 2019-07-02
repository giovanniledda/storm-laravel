<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public function statusable()
    {
        return $this->morphTo();
    }
}
