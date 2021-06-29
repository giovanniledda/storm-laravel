<?php

namespace App\JsonApi\V1\Sections;

use App\Models\Section;
use CloudCreativity\LaravelJsonApi\Eloquent\AbstractAdapter;
use CloudCreativity\LaravelJsonApi\Pagination\StandardStrategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Adapter extends AbstractAdapter
{
    protected $fillable = ['name', 'section_type', 'position', 'code', 'boat_id'];
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
        parent::__construct(new \App\Models\Section(), $paging);
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

    /**
     * Pre-delete hook
     *
     * @param Section $section
     */
    protected function deleting(Section $section)
    {
        abort_if($section->subsections()->count(), 412, __(HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG, ['resource' => 'Section', 'entities' => 'Subsections']));
        abort_if($section->tasks()->count(), 412, __(HTTP_412_ADD_DEL_ENTITIES_ERROR_MSG, ['resource' => 'Section', 'entities' => 'Tasks']));
    }
}
