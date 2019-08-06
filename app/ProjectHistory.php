<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    protected $table = 'project_history';

    protected $fillable = [
       'author_id','project_id','event','event_type'
    ]; 
    
    
    public function author()
    {
        return $this->belongsTo('App\User');
    }

    public function authorNickname()
    {
        // auhtor è un App\User
        return $this->author ? $this->author->getNickname() : '-';
    }
}
