<?php

namespace App\JsonApi\V1\Tasks;

use App\Models\Section;
use App\Models\TaskInterventType;
use App\Models\User;
use Neomerx\JsonApi\Schema\SchemaProvider;

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
        foreach ($detailed_images as $i) {
            $diu[] = $i->getShowApiUrl();
        }

        $giu = [];
        foreach ($generic_images as $i) {
            $giu[] = $i->getShowApiUrl();
        }

        $aiu = [];
        foreach ($additional_images as $i) {
            $aiu[] = $i->getShowApiUrl();
        }

        $gdu = [];
        foreach ($generic_documents as $i) {
            $tmp = [
                'uri' => $i->getShowApiUrl(),
                'title' => $i->title,
                'mime_type' => $i->media->first()->mime_type, // TODO: get MIME TYPE
            ];
            $gdu[] = $tmp;
        }

        $image = $resource->generic_images->last();
        if (! $image) {
            $image = $resource->detailed_images->first();
        }

        return [
            'detailed_images' => $diu,
            'additional_images' => $aiu,
            'generic_documents' => $gdu,
            'image' => $image ? $image->getShowApiUrl() : null,

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
        $resource->setLastHistory(); // ottimizza il processo per una singola invocazione della API
        $author = $resource->author;
        $comments = $resource->comments()->get();
        $section = Section::select('name')->where('id', $resource->section_id)->first();
        $intervent_type = $resource->intervent_type;
        $zone = $resource->zone;
        $closer_app_log = $resource->closer_application_log()->first();
        $opener_app_log = $resource->opener_application_log()->first();

        return [
            'task_type' => $resource->task_type,
            'description' => $resource->description,
            'number' => $resource->number,
            'internal_progressive_number' => $resource->internal_progressive_number,
            'worked_hours' => $resource->worked_hours,
            'estimated_hours' => $resource->estimated_hours,
            'status' => $resource->task_status,
            'author_id' => $resource->author ? $author->id : '',
            'author' => $resource->author ? $author->name.' '.$author->surname : '',
            'last_history' => $resource->getLastHistoryForApi(),
            'last_editor' => $resource->getLastEditor(),
            'last_editor_id' => $resource->getLastEditorId(),
            'is_open' => $resource->is_open,
            'is_private' => $resource->is_private,
            'added_by_storm' => $resource->added_by_storm,
            'project_id' => $resource->project_id,
            'section_id' => $resource->section_id,
            'section_name' => $section->name,
            'intervent_type_id' => $resource->intervent_type_id,
            'intervent_type_name' => $intervent_type ? $intervent_type->name : null,
            'subsection_id' => $resource->subsection_id,
            'x_coord' => $resource->x_coord,
            'y_coord' => $resource->y_coord,
            'comments' => $comments,
            'zone' => $zone,
            'opener_application_log_id' => $opener_app_log ? $opener_app_log->id : null,
            'closer_application_log_id' => $closer_app_log ? $closer_app_log->id : null,
            'opener_application_log_name' => $opener_app_log ? $opener_app_log->name : null,
            'closer_application_log_name' => $closer_app_log ? $closer_app_log->name : null,
            'created-at' => $resource->created_at ? $resource->created_at->toAtomString() : null,
            'updated-at' => $resource->updated_at ? $resource->updated_at->toAtomString() : null,
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
