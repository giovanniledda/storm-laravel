<?php

namespace App\JsonApi\V1\Updates;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{
    /**
     * @var string
     */
    protected $resourceType = 'updates';

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
        $author_data = null;
        if ($author = $resource->getActionAuthor()) {
            $author_data = $author;
            $author_data->hasPhoto = $author->hasProfilePhoto();
            $author_data->profilePhoto = $author->hasPhoto ? $author->getProfilePhotoDocument()->id : null;
        }

        return [
            'update-id' => $resource->getUpdateId(),
            'is-read' => $resource->isRead(),
            'read-at' => $resource->read_at,
            'task-id' => $resource->getTaskId(),
            'boat' => $resource->getBoatName(),
            'boat-id' => $resource->getBoatId(),
            'project' => $resource->getProjectName(),
            'project-id' => $resource->getProjectId(),
            'message' => $resource->getMessage(),
            'author' => $author,
            'created-at' => $resource->created_at->toAtomString(),
            'updated-at' => $resource->updated_at->toAtomString(),
        ];
    }
}
