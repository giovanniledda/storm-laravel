<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use function base64_encode;
use function date;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function time;
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
        'historyable_type',
        'updated_at'
    ];

    public function historyable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }

    public function getFirstcomment()
    {
        return $this->comments()->oldest()->first();
    }

    public function comments_for_api()
    {
        return $this->comments()
            ->join('users', 'users.id', '=', 'comments.author_id')
            ->select(['comments.id',
                'comments.body',
                'comments.created_at',
                'comments.updated_at',
                'users.name as author_name',
                'users.surname  as author_surname']);
    }

    /**
     * Extracts the attribute value from the "event_body" json field
     *
     * @param $attribute
     * @return string|null
     */
    public function getBodyAttribute($attribute)
    {
        $event_body_arr = json_decode($this->event_body, 1);
        if (!empty($event_body_arr)) {
            return isset($event_body_arr[$attribute]) ? $event_body_arr[$attribute] : null;
        }
    }

    public function getMediaPath($media)
    {
        $document = $media->model;
        $media_id = $media->id;

        $history_id = $this->id;
        $path = 'histories' . DIRECTORY_SEPARATOR .
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
            $photo_objects['detailed_images'][] = $this->extractJsonDocumentPhotoInfo($photo_doc);
        }

        $additional_photo_doc = $this->getAdditionalPhotoDocument();
        if ($additional_photo_doc) {
            $photo_objects['additional_images'] = $this->extractJsonDocumentPhotoInfo($additional_photo_doc);
        }

        return ['data' => $photo_objects];
    }

    /**
     * Updates updated_at field
     */
    public function updateLastEdit()
    {
        $this->update(['updated_at' => date('Y-m-d H:i:s', time())]);
    }
}
