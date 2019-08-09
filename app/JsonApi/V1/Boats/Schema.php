<?php

namespace App\JsonApi\V1\Boats;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'boats';

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
        //App\Boat resource
        $project_active = $resource->projects()->where('project_status', PROJECT_STATUS_OPERATIONAL)->first();



        $generic_images = $resource->generic_images;
        $generic_documents = $resource->generic_documents;



        // $giu = [];
        // foreach ($generic_images as $i){
        //     $giu []= $i->getShowApiUrl();
        // }

        $gdu = [];
        foreach ($generic_documents as $i){
            $gdu []= $i->getShowApiUrl();
        }

        $image = $resource->generic_images->last();
        if (!$image) {
            $image = $resource->detailed_images->first();
        }
        return [
            'generic_documents' => $gdu,
            'image' => $image ? $image->getShowApiUrl() : null,
            'project_id'=>($project_active) ? $project_active->id : null
        ];
        // TODO : mettere sia il link documentale all'immagine della barca che il project_id
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
            'registration_number' => $resource->registration_number,
            'flag' => $resource->flag,
            'manufacture_year' => $resource->manufacture_year,
            'length' => $resource->length,
            'draft' => $resource->draft,
            'beam' => $resource->beam,
            'boat_type' => $resource->boat_type,
            'site_id' => $resource->site_id,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }
}
