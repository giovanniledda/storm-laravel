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


        $detailed_images = $resource->detailed_images;
        $generic_images = $resource->generic_images;
        $additional_images = $resource->additional_images;
        $generic_documents = $resource->generic_documents;

        $diu = [];
        foreach ($detailed_images as $i){
            $diu []= $i->getShowApiUrl();
        }

        $giu = [];
        foreach ($generic_images as $i){
             $giu []= $i->getShowApiUrl();
        }

        $aiu = [];
        foreach ($additional_images as $i){
            $aiu []= $i->getShowApiUrl();
        }

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
        if (!$image) {
            $image = $resource->detailed_images->first();
        }
        return [
            'detailed_images' => $diu,
            'additional_images' => $aiu,
            'generic_documents' => $gdu,
            'image' => $image ? $image->getShowApiUrl() : null

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
        $comments = $resource->comments()->get();
        return [

            'description' => $resource->description,
            'number'=> $resource->number,
            'worked_hours'=> $resource->worked_hours,
            'estimated_hours'=> $resource->estimated_hours,
            'status'=> $resource->task_status,
            'author_id'=> $resource->author ? $author->id : '',
            'author'=> $resource->author ? $author->name : '',
            'is_open' => $resource->is_open,
            'added_by_storm' => $resource->added_by_storm,
            'project_id' => $resource->project_id,
            'section_id' => $resource->section_id,
            'intervent_type_id' => $resource->intervent_type_id,
            'subsection_id' => $resource->subsection_id,
            'x_coord' => $resource->x_coord,
            'y_coord' => $resource->y_coord,
            'comments'=> $comments,
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
