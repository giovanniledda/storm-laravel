<?php
namespace App;

use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectTool extends Pivot
{
    protected $table = 'project_tool';

    public $incrementing = true;

    protected $fillable = [
        'project_id',
        'tool_id'
    ];

    public function tool()
    {
        return $this->belongsTo('App\Tool');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    /**
     * @param int $tool_id
     * @param int $project_id
     * @return int
     */
    public static function createOneIfNotExists(int $tool_id, int $project_id)
    {
        try {
            return ProjectTool::create([
                'tool_id' => $tool_id,
                'project_id' => $project_id
            ]);
        } catch (PDOException $e) {
            // se si passa di qua qualcuno cerca di fare una relazione già presente, un doppione.
            return -1;
        }
    }

    /**
     * @param int $tool_id
     * @param int $project_id
     * @return mixed
     */
    public static function findOneByPks(int $tool_id, int $project_id)
    {
        return ProjectTool::where('tool_id', '=', $tool_id)
            ->where('project_id', '=', $project_id)
            ->first();
    }
}
