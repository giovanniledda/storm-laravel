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

    /**
     * @param $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        $author = User::where('id', $resource->author_id)->first();
       
      
        return [
            'title' => $resource->title,
            'description' => $resource->description,
            'number'=> $resource->number,
            'worked_hours'=> $resource->worked_hours,
            'estimated_hours'=> $resource->estimated_hours,
            'status'=> $resource->status,
            'author_id'=> $author->id,
            'author'=> $author->name,
            'project_id' => $resource->project_id,
            'section_id' => $resource->section_id,
            'subsection_id' => $resource->subsection_id,
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
