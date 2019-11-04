<?php

namespace App\JsonApi\V1\TaskMinimized;

use Neomerx\JsonApi\Schema\SchemaProvider;
use App\User;
use App\Section;
use App\TaskInterventType;

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


     public function getPrimaryMetaOld($resource)
    {
         return [];
 
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

    public function getInclusionMetaOld($resource)
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
      // $author   = $resource->author;
      // $comments = $resource->comments()->get();
      // $section  = Section::select("name")->where('id', $resource->section_id)->first();
      // $intervent_types = TaskInterventType::select("name")->where('id', '=', $resource->intervent_type_id)->first();
        return [
        //    'created-at' => $resource->created_at->toAtomString(),
        //    'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }


    /* creare link customizzati */
    public function getResourceLinks($resource)
    {
        return;
        $links = parent::getResourceLinks($resource);
      //  $links['foo'] = $this->createLink('posts/foo');

        return $links;
    }
}
