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
        //App\Boat resource
          
        return [
            'image' => 'https://picsum.photos/200/300', 
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
