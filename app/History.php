<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';

    protected $fillable = [
       'event_body','event_date', 'historyable_id', 'historyable_type'
    ]; 
    
    
     public function historyable()
    {
        return $this->morphTo();
    }
    
   
}
