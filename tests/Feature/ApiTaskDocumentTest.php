<?php

namespace Tests\Feature;

use Tests\TestApiCase;

use App\Project;
use App\Boat;
use App\Task;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiTaskDocumenttTest extends TestApiCase
{
    /** create **/
    function test_can_associate_document_to_task()
    {

        $task_name = $this->faker->sentence;
        $task = new \App\Task(['title'=>$task_name, 'description' => '']);
        $task->save();

        $filename = 'testDocument.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $h = fopen($filepath, 'r');
        $file_content = fread($h, filesize($filepath));
        fclose($h);
        $base64FileContent = base64_encode($file_content);


        $data = [
                    'title' => $filename,
                    'file' => $base64FileContent,
                    'filename' =>  'testDocument.txt'
        ];



        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $this->json('POST', route('api:v1:tasks.createDocument', ['task'=>$task->id]), $data, $headers )
         ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        $document_id = $content['data']['id'];
        $document = \App\Document::find($document_id);

        $this->assertEquals($document->id, $document_id);

        // $this->logResponce($response);

    }
}
