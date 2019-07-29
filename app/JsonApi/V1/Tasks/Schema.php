<?php

namespace App\JsonApi\V1\Tasks;

use Neomerx\JsonApi\Schema\SchemaProvider;
use App\User;

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

    
     public function getPrimaryMeta($resource)
    {
       
       return [
           'image' => 'https://picsum.photos/200/300',  
           
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
//        $author = User::where('id', $resource->author_id)->first();
        $author = $resource->author;

        return [
            'title' => $resource->title,
            'description' => $resource->description,
            'number'=> $resource->number,
            'worked_hours'=> $resource->worked_hours,
            'estimated_hours'=> $resource->estimated_hours,
            'status'=> $resource->task_status,
            'author_id'=> $resource->author ? $author->id : '',
            'author'=> $resource->author ? $author->name : '',
            'project_id' => $resource->project_id,
            'section_id' => $resource->section_id,
            'intervent_type_id' => $resource->intervent_type_id,
            'subsection_id' => $resource->subsection_id,
            'x_coord' => $resource->x_coord,
            'y_coord' => $resource->y_coord,
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
