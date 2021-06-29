<?php

namespace App\JsonApi\V1\Sites;

use function abort_if;
use App\Models\Site;
use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use StormUtils;

class Adapter extends AbstractAdapter
{
    /**
     * Mapping of JSON API attribute field names to model keys.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Adapter constructor.
     *
     * @param StandardStrategy $paging
     */
    public function __construct(StandardStrategy $paging)
    {
        parent::__construct(new \App\Models\Site(), $paging);
    }

    /**
     * @param Builder $query
     * @param Collection $filters
     * @return void
     */
    protected function filter($query, Collection $filters)
    {
        // TODO
    }

//    /**
//     * @var Model $record
//     */
//    protected function destroy($record)
//    {
//        try {
//            $ret = (bool)$record->delete();
//            return $ret;
//        } catch (\Exception $exc) {
    ////            return StormUtils::catchIntegrityContraintViolationException($exc);
//            return StormUtils::jsonAbortWithInternalError(412, 100, 'Precondition failed', HTTP_412_DEL_UPD_ERROR_MSG);
//        }
//    }

    /**
     * Pre-delete hook
     *
     * @param Site $site
     */
    protected function deleting(Site $site)
    {
        abort_if($site->boats()->count(), 412, __(HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG, ['resource' => 'Site', 'entities' => 'Boats']));
        abort_if($site->projects()->count(), 412, __(HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG, ['resource' => 'Site', 'entities' => 'Projects']));
    }
}
