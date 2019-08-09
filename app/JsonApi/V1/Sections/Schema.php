<?php

namespace App\JsonApi\V1\Sections;

use Neomerx\JsonApi\Schema\SchemaProvider;

use App\Boat;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'sections';

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

        $generic_documents = $resource->generic_documents;


        $gdu = [];
        foreach ($generic_documents as $i){
            $tmp =[
                'uri' => $i->getShowApiUrl(),
                'title' => $i->title,
                'mime_type' => $i->media->first()->mime_type // TODO: get MIME TYPE
            ];
            $gdu []= $tmp;
        }
        $image = $resource->generic_images->last();

        return [
            'generic_documents' => $gdu,
            'image' => $image ? $image->getShowApiUrl() : null,

        ];
        // TODO : mettere l'immagine tramite relazione con documents
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
        $boat = Boat::find($resource->boat_id);

        $dimension_fraction =   ($boat->length > 0) && ($boat->draft > 0)  ? $boat->length/$boat->draft : null;


        return [
            'name' => $resource->name,
            'section_type' => $resource->section_type,
            'position' => $resource->position,
            'code' => $resource->code,
            'boat_id' => $resource->boat_id,
            'dimension_factor'=>$dimension_fraction,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),

        ];
    }
}
