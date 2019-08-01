<?php

namespace App\JsonApi\V1\Documents;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'documents';

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

        // $ciccio = print_r (get_class_methods($resource->getFirstMedia('documents')), true);

        return [
            // 'ciccio' => $ciccio,
            'title' => $resource->getFirstMedia('documents')->getPath(),
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }
}
