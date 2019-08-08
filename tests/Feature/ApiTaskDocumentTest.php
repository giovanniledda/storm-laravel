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
use Laravel\Passport\Passport;


use App\Role;
use App\Permission;


use const ROLE_ADMIN;
use const PERMISSION_ADMIN;


class ApiTaskDocumenttTest extends TestApiCase
{
    /** create **/
    function test_can_associate_document_to_task()
    {

        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

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
                    'filename' =>  'testDocument.txt',
                    'type' => 'task_detailed_image'

        ];


        $response = $this->json('POST', route('api:v1:tasks.document', ['task'=>$task->id]), $data, $this->headers );

        // ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        $document_id = $content['data']['id'];
        $document = \App\Document::find($document_id);

        $this->assertEquals($document->id, $document_id);

        // $this->logResponce($response);

    }
}
