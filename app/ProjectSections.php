<?php
namespace App;
 
use Illuminate\Database\Eloquent\Relations\Pivot;
  
class ProjectSections extends Pivot
{
    protected $table = 'project_section';
    public $incrementing = true;
    protected $fillable = [ 
        'section_id',
        'project_id'
    ]; 
} 