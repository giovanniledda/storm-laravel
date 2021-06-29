<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Permission;
use App\Models\Project;
use App\Role;
use App\Models\Task;
use const DOCUMENT_RELATED_ENTITY_TASK;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Net7\Documents\Document;
use const PERMISSION_ADMIN;
use const ROLE_ADMIN;
use Tests\TestApiCase;

class ApiDocumentTest extends TestApiCase
{
    /** create **/
    public function test_can_associate_document_to_task()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $boat = Boat::factory()->create();

        $project = Project::factory()->create();

        $task_name = $this->faker->sentence;
        $task = new \App\Models\Task(['title'=>$task_name, 'description' => '']);

        $task->save();

        $task->project()->associate($project)->save();

        $filename = 'testDocument.txt';
        $filepath = __DIR__.'/'.$filename;
        $h = fopen($filepath, 'r');
        $file_content = fread($h, filesize($filepath));
        fclose($h);
        $base64FileContent = base64_encode($file_content);

        $data = [
            'data' => [
                'attributes' => [
                    'title' => $filename,
                    'file' => $base64FileContent,
                    'filename' =>  'testDocument.txt',
                    'type' => Document::DETAILED_IMAGE_TYPE,
                    'entity_type' => DOCUMENT_RELATED_ENTITY_TASK,
                    'entity_id' => $task->id,
                ],
                'type' => 'tasks',
            ],
        ];

        $response = $this->json('POST', route('api:v1:documents.create'), $data, $this->headers);

        // ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        // TODO: creare tabelle in testing

        $document_id = $content['data']['id'];
        $document = Document::find($document_id);

        $this->assertEquals($document->id, $document_id);
    }

    /** create **/
    public function test_can_associate_document_to_project()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $boat = Boat::factory()->create();

        $project = Project::factory()->create();

        $project->boat()->associate($boat)->save();

        $project->save();

        $filename = 'testDocument.txt';
        $filepath = __DIR__.'/'.$filename;
        $h = fopen($filepath, 'r');
        $file_content = fread($h, filesize($filepath));
        fclose($h);
        $base64FileContent = base64_encode($file_content);

        $data = [
            'data' => [
                'attributes' => [
                    'title' => $filename,
                    'file' => $base64FileContent,
                    'filename' =>  'testDocument.txt',
                    'type' => Document::DETAILED_IMAGE_TYPE,
                    'entity_type' => DOCUMENT_RELATED_ENTITY_PROJECT,
                    'entity_id' => $project->id,
                ],
                'type' => 'tasks',
            ],
        ];

        $response = $this->json('POST', route('api:v1:documents.create'), $data, $this->headers);

        // ->assertJsonStructure(['data' => ['id']]);

        $content = json_decode($response->getContent(), true);

        $document_id = $content['data']['id'];
        $document = Document::find($document_id);

        $this->assertEquals($document->id, $document_id);
    }

    /** cannot create **/
    public function test_cannot_associate_document_to_non_existent_project()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $filename = 'testDocument.txt';
        $filepath = __DIR__.'/'.$filename;
        $h = fopen($filepath, 'r');
        $file_content = fread($h, filesize($filepath));
        fclose($h);
        $base64FileContent = base64_encode($file_content);

        $data = [
             'data' => [
                 'attributes' => [
                     'title' => $filename,
                     'file' => $base64FileContent,
                     'filename' =>  'testDocument.txt',
                     'type' => Document::DETAILED_IMAGE_TYPE,
                     'entity_type' => DOCUMENT_RELATED_ENTITY_TASK,
                     'entity_id' => 8989123,
                 ],
                 'type' => 'tasks',
             ],
         ];

        $response = $this->json('POST', route('api:v1:documents.create'), $data, $this->headers)

         ->assertJsonStructure(['errors']);
    }

    /** cannot create **/
    public function test_cannot_associate_document_to_non_existent_entity_type()
    {
        Role::firstOrCreate(['name' => ROLE_ADMIN]);
        Permission::firstOrCreate(['name' => PERMISSION_ADMIN]);
        $admin1 = $this->_addUser(ROLE_ADMIN);
        $token_admin = $this->_grantTokenPassword($admin1);
        $this->assertIsString($token_admin);
        Passport::actingAs($admin1);

        $filename = 'testDocument.txt';
        $filepath = __DIR__.'/'.$filename;
        $h = fopen($filepath, 'r');
        $file_content = fread($h, filesize($filepath));
        fclose($h);
        $base64FileContent = base64_encode($file_content);

        $data = [
                'data' => [
                    'attributes' => [
                        'title' => $filename,
                        'file' => $base64FileContent,
                        'filename' =>  'testDocument.txt',
                        'type' => Document::DETAILED_IMAGE_TYPE,
                        'entity_type' => 'non_existent_one',
                        'entity_id' => 1,
                    ],
                    'type' => 'tasks',
                ],
            ];

        $response = $this->json('POST', route('api:v1:documents.create'), $data, $this->headers)
            ->assertJsonStructure(['errors']);

        $content = json_decode($response->getContent(), true);
    }
}
