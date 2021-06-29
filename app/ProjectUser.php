<?php

namespace App;

use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{
    protected $table = 'project_user';

    public $incrementing = true;

    protected $fillable = [
        // 'role',
        'profession_id',
        'project_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Project::class);
    }

    public function profession()
    {
        return $this->belongsTo(\App\Profession::class);
    }

    /**
     * @param int $user
     * @param int $project
     * @param int $profession
     * @return int
     */
    public static function createOneIfNotExists(int $user_id, int $project_id, int $profession_id = null)
    {
        try {
            return self::create([
                'user_id' => $user_id,
                'project_id' => $project_id,
                'profession_id' => $profession_id,
            ]);
        } catch (PDOException $e) {
            // se si passa di qua, si è violata la chiave $table->unique(['registry_id', 'work_group_id']);
            // ovvero: qualcuno cerca di fare una relazione già presente, un doppione.
            return -1;
        }
    }

    /**
     * @param int $user
     * @param int $project
     * @param int $profession
     * @return mixed
     */
    public static function findOneByPks(int $user_id, int $project_id, int $profession_id)
    {
        return self::where('user_id', '=', $user_id)
            ->where('project_id', '=', $project_id)
            ->where('profession_id', '=', $profession_id)
            ->first();
    }
}
