<?php

namespace App\Http\Controllers\Api;

use App\History;
use App\Http\Controllers\Controller;
use App\Section;
use App\Task;
use App\User;
use App\Utils\Utils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use function is_null;
use Net7\Documents\Document;
use const TASK_TYPE_PRIMARY;
use const TASK_TYPE_REMARK;
use const TASKS_STATUSES;
use Validator;

class TaskController extends Controller
{
    public function primaryStatuses(Request $request)
    {
        return Utils::renderStandardJsonapiResponse([
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'task-type' => TASK_TYPE_PRIMARY,
                    'task-statuses' => TASKS_STATUSES,
                ],
            ], ], 201);
    }

    public function remarkStatuses(Request $request)
    {
        return Utils::renderStandardJsonapiResponse([
            'data' => [
                'type' => 'tasks',
                'attributes' => [
                    'task-type' => TASK_TYPE_REMARK,
                    'task-statuses' => TASKS_R_STATUSES,
                ],
            ], ], 201);
    }

    public function history(Request $request, $related)
    {
        $task = json_decode($related, true);
        $histories = Task::find($task['id'])->history()->orderBy('event_date', 'DESC')->get()->toArray();
        $data = [];

        foreach ($histories as $history) {
            $history_data = array_merge(json_decode($history['event_body'], true), ['event_date' => $history['event_date']]);

            $resource = History::find($history['id']);
            $history_data['photos'] = $resource->getPhotosApi();
            $history_data['comments'] = $resource->comments_for_api;

            array_push($data, [
                'type' => 'history',
                'id' => $history['id'],
                'attributes' => $history_data, ]);
        }

        return Utils::renderStandardJsonapiResponse(['data' => $data], 200);
    }

    public function generateMap(Request $request, $related)
    {
//        $task = Task::findOrFail($related->id);
        $task = $request->record;

        return $task->updateMap();

        exit();

        //Scrivi un messaggio

        //  $task = json_decode($related, true);
        // prendo l'immagine del ponte
        /*     $isOpen = $task['is_open'];
             $status = $task['task_status'];

             $section = Section::find($task['section_id']);
             $bridgeMedia = $section->generic_images->last();

             $bridgeImagePath = $bridgeMedia->getPathBySize('');
             $bridgeImageInfo = getimagesize($bridgeImagePath);
             $image = imagecreate ($bridgeImageInfo[0] ,$bridgeImageInfo[1]  ) ;
                      imagecolorallocate (  $image ,255,255 , 255 );

             // sfondo bianco
            // $im = @imagecreate(110, 20)  or die("Cannot Initialize new GD image stream");
            // $background_color = imagecolorallocate($im, 255, 255, 255);

             if (exif_imagetype($bridgeImagePath) === IMAGETYPE_PNG) {
                 // il ponte e' un'immagine png
                 $dest = imagecreatefrompng($bridgeImagePath);
             }

             if (exif_imagetype($bridgeImagePath) === IMAGETYPE_JPEG) {
                 // il ponte e' un'immagine jpg
                 $dest = imagecreatefromjpeg($bridgeImagePath);
             }
             imagecopy($image, $dest, 0, 0, 0, 0, $bridgeImageInfo[0] ,$bridgeImageInfo[1]);
             try {

                 $pinPath = $this->getIcon($status, $isOpen);
                 $iconInfo = getimagesize($pinPath);
                 $src = imagecreatefrompng($pinPath);
                 //  imagecopymerge($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
                 imagecopymerge($image, $src, $task['y_coord' ] - $iconInfo[0], $bridgeImageInfo[1] - $task['x_coord'] - $iconInfo[1], 0, 0, $iconInfo[0], $iconInfo[1], 75);

                // imagepng($image, storage_path() . DIRECTORY_SEPARATOR . 'pippo.png');
             //   $im = imagecreatefrompng(storage_path() . DIRECTORY_SEPARATOR . 'pippo.png');
                 $size = min(imagesx($image), imagesy($image));
                 $im2 = imagecrop($image, ['x' => ( $task['y_coord'] -  ( $bridgeImageInfo[0]/2 ) /2 ) + ( $iconInfo[0] /2 ) , 'y' =>   ( $bridgeImageInfo[1]/2 ) + $task['x_coord']  - $iconInfo[1] , 'width' => $bridgeImageInfo[0]/2, 'height' => $bridgeImageInfo[1]/2]);
                 if ($im2 !== FALSE) {
                     imagepng($im2, storage_path() . DIRECTORY_SEPARATOR . 'cropped.png');
                     imagedestroy($im2);
                 }
                 imagedestroy($dest);
                 imagedestroy($src);
             } catch (\Exception $exc) {
                 echo $exc->getMessage();
             }



             //$resp = Response(["bridge"=> $bridgeImagePath,
             //              'storage' => storage_path(), 'pin' => $this->getIcon($status , $isOpen) ], 200);
             //$resp->header('Content-Type', 'application/vnd.api+json');
             //  return $resp;
             //  var_dump($task);
             $resp = Response(['x' =>  $task['x_coord'] , 'y' => $task['y_coord'], 'size'=> $size, 'imgsize' =>$bridgeImageInfo , $task ], 200);
             $resp->header('Content-Type', 'application/vnd.api+json');

             return $resp;*/
    }

    private function getIcon($status, $isOpen)
    {
        $path = storage_path().DIRECTORY_SEPARATOR.'storm-pins';

        return $path.DIRECTORY_SEPARATOR.'Accepted'.DIRECTORY_SEPARATOR.'Active.png';
    }

    /**
     * Revert task status to previous one, based on history list
     *
     * #T11 /api/v1/tasks/{record_id}/undo-status-change
     *
     * @param Request $request
     * @param Task $record
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function undoStatusChange(Request $request, $record)
    {
        /** @var History $last_history */
        $last_history = $record->getLastHistory();
        $original_task_status = $last_history->getBodyAttribute('original_task_status');
        if (! is_null($original_task_status) && $record->task_status != $original_task_status) {
            $record->update(['task_status' => $original_task_status]);
        }

        return Utils::renderStandardJsonapiResponse([
            'data' => [
                'type' => 'tasks',
                'id' => $record->id,
                'attributes' => ['task-status' => $record->task_status],
            ], ], 200);
    }
}
