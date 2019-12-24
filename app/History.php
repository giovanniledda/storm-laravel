<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use function base64_encode;
use function file_exists;
use function file_get_contents;
use const DIRECTORY_SEPARATOR;

class History extends Model
{
    use DocumentableTrait;

    protected $_photo_documents_size = ''; // 'thumb'; TODO: a regime mettere thumb (in locale va solo se si azionano le code)

    protected $table = 'history';

    protected $fillable = [
        'event_body',
        'event_date',
        'historyable_id',
        'historyable_type'
    ];

    public function historyable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function comments_for_api()
    {
        return $this->comments()
            ->select(['comments.id', 'comments.body', 'comments.created_at', 'users.name as author_name', 'users.surname  as author_surname'])
            ->join('users', 'users.id', '=', 'comments.author_id');
    }

    public function getMediaPath($media)
    {
        $document = $media->model;
        $media_id = $media->id;

        $task = $this->historyable;
        $task_id = $task->id;
        $history_id = $this->id;
        $path = 'tasks' . DIRECTORY_SEPARATOR . $task_id . DIRECTORY_SEPARATOR . 'histories' . DIRECTORY_SEPARATOR .
            $history_id . DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * @return mixed
     */
    public function getAdditionalPhotoMedia()
    {
        return $this->getDocumentMediaFile(Document::ADDITIONAL_IMAGE_TYPE);
    }

    /**
     * @return mixed
     */
    public function getDetailedPhotoMedias()
    {
        return $this->getAllDocumentsMediaFileArray(Document::DETAILED_IMAGE_TYPE);
    }


    /**
     * @return mixed
     */
    public function getAdditionalPhotoDocument()
    {
        return $this->getDocument(Document::ADDITIONAL_IMAGE_TYPE);
    }

    /**
     * @return mixed
     */
    public function getDetailedPhotoDocument()
    {
        return $this->getAllDocumentsByType(Document::DETAILED_IMAGE_TYPE);
    }

    /**
     * @param Document $photo_doc
     * @return array
     */
    protected function extractJsonDocumentPhotoInfo(Document $photo_doc)
    {
        $media = $photo_doc->getRelatedMedia();
        $file_path = $media->getPath($this->_photo_documents_size);
        return !file_exists($file_path) ? [] : [
            'type' => 'documents',
            'id' => $photo_doc->id,
            'attributes' => [
                'doc_type' => $photo_doc->type,
                'base64' => base64_encode(file_get_contents($file_path))
            ]
        ];
    }

    /**
     * Return an array of base64 media objects
     *
     * @return array
     */
    public function getPhotosApi()
    {
        $photo_objects = [];
        $detailed_photo_docs = $this->getDetailedPhotoDocument();
        foreach ($detailed_photo_docs as $photo_doc) {
            $photo_objects[] = $this->extractJsonDocumentPhotoInfo($photo_doc);
        }

        $additional_photo_doc = $this->getAdditionalPhotoDocument();
        if ($additional_photo_doc) {
            $photo_objects[] = $this->extractJsonDocumentPhotoInfo($additional_photo_doc);
        }

        return ['data' => $photo_objects];
    }
}
