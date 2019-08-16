<?php
namespace App;
 
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{
    protected $table = 'project_user';

    public $incrementing = true;

    protected $fillable = [
        // 'role',
        'profession_id',
        'project_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function profession()
    {
        return $this->belongsTo('App\Profession');
    }
} 