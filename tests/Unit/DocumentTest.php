<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentTest extends TestCase
{


    /**
     * A basic unit test example.
     *
     * @return void
     */

     /*
     public function test_can_create_document(){
        $this->disableExceptionHandling();



        $this->assertEquals([], Storage::allFiles());
        

        $sizeInKilobytes = 200;

        $filename = 'document.pdf';
        $nonExistingFilename = 'missing.doc';

        $file = UploadedFile::fake()->create( $filename, $sizeInKilobytes);

        $fake_title = $this->faker->sentence;
        $doc = new \App\Document([
            'title' => $fake_title,
            'file' => $file
        ]);

        $doc->save();
        $this->assertDatabaseHas('documents', ['title' =>  $fake_title] );

        $this->assertEquals(1, $doc->getMedia('documents')->count());

        // $this->assertFileExists($doc->getFirstMedia('media')->getPath());

        // Assert the file was stored...
        $this->assertFileExists($doc->getFirstMedia('documents')->getPath());

        // Assert a file does not exist...
        // Storage::disk('local')->assertMissing ($nonExistingFilename);
        // $this->assertNotEquals([], Storage::allFiles());

     }


     public function test_can_create_document_via_json_api()
    {

        $this->disableExceptionHandling();

        // $local_file = __DIR__ . '/../Feature/text_file.txt';

        // $uploadedFile = new UploadedFile(
        //     $local_file,
        //     'text_file.txt',
        //     'text/plain',
        //     null,
        //     null,
        //     true
        // );


        $fake_title = $this->faker->sentence;
        $data = [
            'data' => [
                'attributes' => [
                    'title' => $fake_title,
                ],
                'type' => 'documents'
                // 'file' => $uploadedFile
            ]
        ];

        $headers = [
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            // 'Transfer-Encoding' => 'chunked'
        ];


        $response = $this->json('POST', route('api:v1:documents.create'), $data, $headers);

        $response
        ->assertStatus(201)
        ->assertJson([
            'data' => [
                'attributes'=> [
                    'title' => $fake_title
                ]
            ]
        ]);


        $content =  json_decode($response->getContent(), true);
        $document_id = $content['data']['id'];

        $this->assertDatabaseHas('documents', ['id' =>  $document_id] );
        $this->assertDatabaseHas('documents', ['title' =>  $fake_title] );

        $document = \App\Document::find($document_id);

    }
*/
    public function test_can_add_files_via_json_api (){
        $this->disableExceptionHandling();

        $sizeInKilobytes = 200;

        $filename = 'document.pdf';
        $nonExistantFilename = 'missing.doc';

        $file = UploadedFile::fake()->create( $filename, $sizeInKilobytes);

        $fake_title = $this->faker->sentence;
        $data = [
            'data' => [
                'attributes' => [
                    'title' => $fake_title,
                    'file' => $file
                ],
                'type' => 'documents'
                // 'file' => $uploadedFile
            ]
        ];

        $headers = [
            'Content-type' => 'multipart/form-data',
            'Accept' => 'application/vnd.api+json',
            // 'Transfer-Encoding' => 'chunked'
        ];

        $response = $this->json('POST', route('api:v1:documents.create'), $data, $headers);

        $content =  json_decode($response->getContent(), true);
        $document_id = $content['data']['id'];
        $doc = \App\Document::find($document_id);

        // print_r ($response->getContent());
        $this->assertFileExists($doc->getFirstMedia('documents')->getPath());

    }

    // public function test_un_test(){
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }


}
