<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

class DocumentController extends Controller
{


    /**
     * Create a document and associate it to task {task} in request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createRelatedToTask(Request $request){

        $task = \App\Task::find($request->task);
        // TODO check task exists
        $title = $request->title;
        $base64File = $request->file;
        $filename = $request->filename;

        if ($base64File) {
            $tmpFilename = uniqid('phpfile_') ;
            $tmpFileFullPath = '/tmp/'. $tmpFilename;
            $h = fopen ($tmpFileFullPath, 'w');
            $decoded = base64_decode($base64File);
            fwrite($h, $decoded, strlen($decoded));
            fclose($h);
        }


        $file =  new UploadedFile( $tmpFileFullPath, $filename, null ,null, true);
        // la seguente riga crea un file nel file system definito in .env
        // https://hotexamples.com/examples/illuminate.http/UploadedFile/-/php-uploadedfile-class-examples.html
        // self::move($file, storage_path('app') . DIRECTORY_SEPARATOR. $filename);

        $doc = new Document([
            'title' => $filename,
            'file' => $file
        ]);

        $doc->save();
        $task->documents()->save($doc);


        $ret = ['data' => [
            'id' => $doc->id,
        ]];
        return new Response($ret, 201);
    }

    /* registro il file su storage
     * attenzione !!! bisogna riscrivere questa parte per drop box e prendere la configurazione
     */
    // public static function move(UploadedFile $file, $path)
    // {
    //      // env('MEDIA_DISK', 'local');

    //     file_put_contents($path , file_get_contents($file->getRealPath() ));

    // }

}
