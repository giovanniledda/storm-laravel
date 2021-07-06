<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use function base64_encode;
use function copy;
use function explode;
use function file_exists;
use function file_get_contents;
use Illuminate\Support\Arr;
use Net7\Documents\Document;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait JsonAPIPhotos
{
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
     * @param null $image_size
     * @return array|string
     */
    public function getDocumentPhotoRelatedMediaPathBySize(Document $photo_doc, $image_size = null)
    {
        $media = $photo_doc->getRelatedMedia();
        if ($media) {
            $file_path = $media->getPath($image_size ?? $this->{'_photo_documents_size'});
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        return null;
    }

    /**
     * @param Document $photo_doc
     * @param null $image_size
     * @return array
     */
    public function extractJsonDocumentPhotoInfo(Document $photo_doc, $image_size = null)
    {
        $file_path = $this->getDocumentPhotoRelatedMediaPathBySize($photo_doc, $image_size);
        if ($file_path) {
            return [
                'type' => 'documents',
                'id' => $photo_doc->id,
                'attributes' => [
                    'doc_type' => $photo_doc->type,
                    'file_path' => $file_path,
                    'base64' => base64_encode(file_get_contents($file_path)),  // before S3
//                    'base64' => base64_encode(Storage::get($file_path)),  // with S3
                ],
            ];
        }

        return null;
    }

    /**
     * A differenza del metodo extractJsonDocumentPhotoInfo, restituisce un link al documento e non il base64
     * @param Document $photo_doc
     * @return array
     */
    public function extractJsonDocumentPhotoInfoLight(Document $photo_doc, $image_size = null)
    {
        $file_path = $this->getDocumentPhotoRelatedMediaPathBySize($photo_doc, $image_size);
        if ($file_path) {
            return [
                'type' => 'documents',
                'id' => $photo_doc->id,
                'attributes' => [
                    'doc_type' => $photo_doc->type,
                    'file_path' => $file_path,
                    'file_uri' => route('download_document_web', $photo_doc->id),
                ],
            ];
        }

        return null;
    }

    /**
     * Return an array of base64 media objects
     *
     * @param string $field_key
     * @param null $image_size
     * @param bool $lightVersion
     * @return array
     */
    public function getPhotosApi($field_key = 'data', $image_size = null, $lightVersion = false)
    {
        $photo_objects = [];
        $detailed_photo_docs = $this->getDetailedPhotoDocument();
        foreach ($detailed_photo_docs as $photo_doc) {
            if ($arr = $lightVersion ? $this->extractJsonDocumentPhotoInfoLight($photo_doc, $image_size) : $this->extractJsonDocumentPhotoInfo($photo_doc, $image_size)) {
                $photo_objects['detailed_images'][] = $arr;
            }
        }

        $additional_photo_doc = $this->getAdditionalPhotoDocument();
        if ($additional_photo_doc) {
            if ($arr = $lightVersion ? $this->extractJsonDocumentPhotoInfoLight($additional_photo_doc, $image_size) : $this->extractJsonDocumentPhotoInfo($additional_photo_doc, $image_size)) {
                $photo_objects['additional_images'][] = $arr;
            }
        }

        return [$field_key => $photo_objects];
    }

    /**
     * Adds an image as a generic_image Net7/Document
     * @param string $filepath
     * @param string|null $type
     * @return Document
     */
    public function addPhoto(string $filepath, string $type = null)
    {
        // mettere tutto in una funzione
        $f_arr = explode('/', $filepath);
        $filename = Arr::last($f_arr);
        $tempFilepath = '/tmp/'.$filename;
        copy('./storage/seeder/'.$filepath, $tempFilepath);
        $file = new UploadedFile($tempFilepath, $filename, null, null, true);

        $doc = new Document([
            'title' => "Photo for block {$this->id}",
            'file' => $file,
        ]);
        $this->addDocumentWithType($doc, $type ? $type : Document::DETAILED_IMAGE_TYPE);

        return $doc;
    }
}
