<?php
namespace Net7\Documents\PathGenerators;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class Net7DocumentsMediaPathGenerator implements PathGenerator
{


    public function getPath(Media $media) : string
    {
        $document = $media->model;
        $model = $document->documentable;

        $path = 'media/';

        try {
            $path .= $model->getMediaPath($media);
        } catch (Exception $e) {
            $path .= DIRECTORY_SEPARATOR . 'lost_and_found_documents' . DIRECTORY_SEPARATOR;
        }


        return $path;
    }



    public function getPathForConversions(Media $media) : string
    {
        return $this->getPath($media).'c' . DIRECTORY_SEPARATOR;
    }
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).DIRECTORY_SEPARATOR.'cri'.DIRECTORY_SEPARATOR;
    }
}
