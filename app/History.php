<?php

namespace App;

use App\Traits\JsonAPIPhotos;
use function copy;
use function date;
use const DIRECTORY_SEPARATOR;
use function explode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use function json_decode;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function time;

class History extends Model
{
    use DocumentableTrait, JsonAPIPhotos;

    protected $_photo_documents_size = ''; // 'thumb'; TODO: a regime mettere thumb (in locale va solo se si azionano le code)

    protected $table = 'history';

    protected $fillable = [
        'event_body',
        'event_date',
        'historyable_id',
        'historyable_type',
        'updated_at',
    ];

    public function historyable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->morphMany(\App\Comment::class, 'commentable');
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
                'users.surname  as author_surname', ]);
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
        if (! empty($event_body_arr)) {
            return isset($event_body_arr[$attribute]) ? $event_body_arr[$attribute] : null;
        }
    }

    public function getMediaPath($media)
    {
        $document = $media->model;
        $media_id = $media->id;

        $history_id = $this->id;
        $path = 'histories'.DIRECTORY_SEPARATOR.
            $history_id.DIRECTORY_SEPARATOR.$document->type.DIRECTORY_SEPARATOR.$media_id.DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Updates updated_at field
     */
    public function updateLastEdit()
    {
        $this->update(['updated_at' => date('Y-m-d H:i:s', time())]);
    }

    /**
     * Retrieve additional image's path
     *
     * @return string
     */
    public function getAdditionalPhotoPath()
    {
        return $this->getDocumentMediaFilePath(Document::ADDITIONAL_IMAGE_TYPE, 'report-large');
    }

    /**
     * Retrieve detailed image's path
     *
     * @return string
     */
    public function getDetailedPhotoPaths()
    {
        return $this->getAllDocumentsMediaFilePathArray(Document::DETAILED_IMAGE_TYPE, 'report');
    }

    /**
     * Adds an image as a generic_image Net7/Document
     * @param string $filepath
     * @param string|null $type
     * @return Document
     */
    public function addDamageReportPhoto(string $filepath, string $type = null)
    {
        // mettere tutto in una funzione
        $f_arr = explode('/', $filepath);
        $filename = Arr::last($f_arr);
        $tempFilepath = '/tmp/'.$filename;
        copy('./storage/seeder/'.$filepath, $tempFilepath);
        $file = new UploadedFile($tempFilepath, $filename, null, null, true);

        $doc = new Document([
            'title' => "Damage photo for History {$this->id}",
            'file' => $file,
        ]);
        $this->addDocumentWithType($doc, $type ? $type : Document::GENERIC_IMAGE_TYPE);

        return $doc;
    }
}
