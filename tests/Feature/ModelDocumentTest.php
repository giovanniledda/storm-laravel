<?php

namespace Tests\Unit;

use App\Document;
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

     public function test_can_create_document(){
        $this->disableExceptionHandling();

        // $this->assertEquals([], Storage::allFiles());

        $sizeInKilobytes = 200;

        $filename = 'document.pdf';
        $nonExistingFilename = 'missing.doc';

        $file = UploadedFile::fake()->create( $filename, $sizeInKilobytes);


        $fake_title = $this->faker->sentence;
        $doc = new Document([
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


    public function test_can_create_document_via_json_api (){


        $this->disableExceptionHandling();

        $sizeInKilobytes = 200;

        $filename = 'document.pdf';
        $nonExistantFilename = 'missing.doc';

        $file = UploadedFile::fake()->create( $filename, $sizeInKilobytes);

        $fake_title = $this->faker->sentence;


        $filename = __DIR__ . '/'. 'testDocument.txt';
        $h = fopen($filename, 'r');
        $file_content = fread($h, filesize($filename));
        fclose($h);
        $base64FileContent = base64_encode($file_content);


        $data = [
            'data' => [
                'attributes' => [
                    'title' => $fake_title,
                    'file' => $base64FileContent
                ],
                'type' => 'documents',
                // 'file' => $uploadedFile
            ]
        ];

        // $data = [
        //     'title' => $fake_title,
        //     'file' => $base64FileContent
        // ];

        $headers = [
            'Content-type' => 'multipart/form-data',
            // 'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            // 'Transfer-Encoding' => 'chunked'
        ];

        // $response = $this->json('POST', route('api:v1:documents.create'), $data, $headers);
        $response = $this->post(route('api:v1:documents.create'), $data, $headers);

        $content =  json_decode($response->getContent(), true);


        $document_id = $content['id'];

        $doc = \App\Document::find($document_id);

        $this->assertEquals($doc->id, $document_id);
        // print_r ($response->getContent());
        $this->assertFileExists($doc->getFirstMedia('documents')->getPath());

    }


}
