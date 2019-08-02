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

} 