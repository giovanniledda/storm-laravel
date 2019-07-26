<?php

namespace App\JsonApi\V1\Projects;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'projects';

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string)$resource->getRouteKey();
    }


    public function getRelationships($post, $isPrimary, array $includeRelationships)
    {
        return [
            'tasks' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ],
        ];
    }

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            'name' => $resource->name,
            'boat_id' => $resource->boat_id,
            'status' => $resource->status,
            'project_type' => $resource->project_type,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }

    /* creare link customizzati */
    public function getResourceLinks($resource)
    {
        $links = parent::getResourceLinks($resource);

        return $links;
    }
}
