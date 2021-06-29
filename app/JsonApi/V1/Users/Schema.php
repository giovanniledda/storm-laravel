<?php

namespace App\JsonApi\V1\Users;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{
    /**
     * @var string
     */
    protected $resourceType = 'users';

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    public function getPrimaryMeta($resource)
    {
        $image = $resource->generic_images->last();

        return [
            'image' => $image ? $image->getShowApiUrl() : null,
        ];
    }

    public function getInclusionMeta($resource)
    {
        return $this->getPrimaryMeta($resource);
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
            'surname' => $resource->surname,
            'email' => $resource->email,
            'is_storm' => $resource->is_storm,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }
}
