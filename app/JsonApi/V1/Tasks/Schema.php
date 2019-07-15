<?php

namespace App\JsonApi\V1\Tasks;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'tasks';

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {

        return [
            'title' => $resource->title,
            'description' => $resource->description,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }


    /* creare link customizzati */
    public function getResourceLinks($resource)
    {
        $links = parent::getResourceLinks($resource);
      //  $links['foo'] = $this->createLink('posts/foo');

        return $links;
    }
}