<?php

namespace App\JsonApi\V1\Projects;

use Neomerx\JsonApi\Schema\SchemaProvider;

use App\Boat;

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

    public function getPrimaryMeta($resource)
    {


        $generic_documents = $resource->generic_documents;


        $gdu = [];
        foreach ($generic_documents as $i){
            $tmp =[
                'uri' => $i->getShowApiUrl(),
                'title' => $i->title,
                'mime_type' => $i->media->first()->mime_type,
                'date' => $i->updated_at->toAtomString(),// TODO: get MIME TYPE
            ];
            $gdu []= $tmp;
        }

        return [
            'generic_documents' => $gdu,

        ];
        // TODO : mettere sia il link documentale all'immagine della barca che il project_id
    }

    public function getInclusionMeta($resource)
    {

        return $this->getPrimaryMeta($resource);
    }

    public function getRelationships($project, $isPrimary, array $includeRelationships)
    {


        return [
            'tasks' => [
             //   self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ]
        ];
    }

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        $boat =  Boat::find($resource->boat_id);

        return [
            'name' => $resource->name,
            'boat_id' => $resource->boat_id,
            'project_id' => $resource->id,
            'boat' =>$boat,
            'status' => $resource->project_status,
            'imported'  => $resource->imported,
            'project_progress'=> $resource->project_progress,
            'project_type' => $resource->project_type,
            'start_date' => $resource->start_date,
            'end_date' => $resource->end_date,
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
