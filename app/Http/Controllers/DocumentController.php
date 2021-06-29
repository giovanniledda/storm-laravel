<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Net7\Documents\Document;

class DocumentController extends Controller
{
    /**
     * Create a document and associate it to task {task} in request
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function createRelatedToTask(Request $request, $related)
    {
        $task = \App\Models\Task::find($request->task);
        // TODO check task exists
        $title = $request->title;
        $base64File = $request->file;
        $filename = $request->filename;

        $tmpFileFullPath = '';
        if ($base64File) {
            $tmpFilename = uniqid('phpfile_');
            $tmpFileFullPath = '/tmp/'.$tmpFilename;
            $h = fopen($tmpFileFullPath, 'w');
            $decoded = base64_decode($base64File);
            fwrite($h, $decoded, strlen($decoded));
            fclose($h);
        }

        $file = new UploadedFile($tmpFileFullPath, $filename, null, null, true);

        $doc = new Document([
            'title' => $filename,
            'file' => $file,
        ]);

        $doc->save();
        $task->documents()->save($doc);

        $ret = ['data' => [
            'id' => $doc->id,
        ]];

        return new Response($ret, 201);
    }
}
