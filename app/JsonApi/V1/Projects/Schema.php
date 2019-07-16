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
        return (string) $resource->getRouteKey();
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
            'boatid' => $resource->boat_id,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }

/* creare link customizzati */
public function getResourceLinks($resource)
{
    $links = parent::getResourceLinks($resource);
    $links['foo'] = $this->createLink('posts/foo');

    return $links;
}
}
