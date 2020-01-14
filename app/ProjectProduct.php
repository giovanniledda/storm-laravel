<?php
namespace App;

use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectProduct extends Pivot
{
    protected $table = 'project_product';

    public $incrementing = true;

    protected $fillable = [
        'project_id',
        'product_id'
    ];

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    /**
     * @param int $product_id
     * @param int $project_id
     * @return int
     */
    public static function createOneIfNotExists(int $product_id, int $project_id)
    {
        try {
            return ProjectProduct::create([
                'product_id' => $product_id,
                'project_id' => $project_id
            ]);
        } catch (PDOException $e) {
            // se si passa di qua qualcuno cerca di fare una relazione già presente, un doppione.
            return -1;
        }
    }

    /**
     * @param int $product_id
     * @param int $project_id
     * @return mixed
     */
    public static function findOneByPks(int $product_id, int $project_id)
    {
        return ProjectProduct::where('product_id', '=', $product_id)
            ->where('project_id', '=', $project_id)
            ->first();
    }
}
