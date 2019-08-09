<?php
namespace App\PathGenerators;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;
class StormMediaPathGenerator implements PathGenerator
{


    public function getPath(Media $media) : string
    {

        $document = $media->model;
        $model = $document->documentable;
        $media_id = $media->id;
        // we put the file in some dir depending on the related object

        $path = 'media/';

        switch (get_class($model)){
            case "App\Task":

                $task = $model;
                $project = $task->project;
                $project_id = $project->id;
                $task_id = $task->id;
                $path .= 'projects' . DIRECTORY_SEPARATOR . $project_id . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR .
                        $task_id . DIRECTORY_SEPARATOR . $document->type . DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

           break;

           case "App\Project":

                $project = $model;
                $project_id = $project->id;
                $path .= 'projects' . DIRECTORY_SEPARATOR . $project_id . DIRECTORY_SEPARATOR . $document->type .
                         DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;

            break;

            case "App\Boat":

                $boat = $model;
                $boat_id = $boat->id;
                $path .= 'boats' . DIRECTORY_SEPARATOR . $boat_id . DIRECTORY_SEPARATOR . $document->type .
                       DIRECTORY_SEPARATOR . $media_id . DIRECTORY_SEPARATOR;
            break;

           default:
                $path .= DIRECTORY_SEPARATOR . 'lost_and_found_documents' . DIRECTORY_SEPARATOR;
           break;
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
