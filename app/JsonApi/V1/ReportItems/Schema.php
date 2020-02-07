<?php

namespace App\JsonApi\V1\ReportItems;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'report-items';

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
            'report_type' => $resource->report_type,
            'report_id' => $resource->report_id,
            'report_name' => $resource->getReportName(),
            'data_attributes' => $resource->getDataAttributes(),
            'report_links' => $resource->getReportLinks(),
            'report_create_date' => $resource->report_create_date,
            'report_update_date' => $resource->report_update_date,
            'author' => $resource->author_for_api(),
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }
}
