<?php

namespace Tests\Unit;

use Net7\Documents\Document;
use Tests\TestApiCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiDocumentTest extends TestApiCase
{


    public function dont_test_can_add_document_to_site(){




    }

    public function test_can_create_document_via_json_api (){


        $this->disableExceptionHandling();




        // $this->withoutExceptionHandling();


        // $sizeInKilobytes = 200;

        // $filename = 'document.pdf';
        // $nonExistantFilename = 'missing.doc';

        // $file = UploadedFile::fake()->create( $filename, $sizeInKilobytes);

        // $fake_title = $this->faker->sentence;


        $filename = 'testDocument.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $h = fopen($filepath, 'r');
        $file_content = fread($h, filesize($filepath));
        fclose($h);
        $base64FileContent = base64_encode($file_content);


        $data = [
            'data' => [
                'attributes' => [
                    'title' => $filename,
                    'file' => $base64FileContent,
                    'filename' =>  'testDocument.txt'
                ],
                'type' => 'documents',
            ]
        ];

        // $data = [
        //     'title' => $fake_title,
        //     'file' => $base64FileContent
        // ];

/*

$headers = [
            // 'Content-type' => 'multipart/form-data',
            'Content-type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
            // 'Transfer-Encoding' => 'chunked'
        ];
*/

       $response = $this->json('POST', route('api:v1:documents.create'), $data, $this->headers);

        // $response = $this->post(route('api:v1:documents.create'), $data,  $this->headers);

        $content =  json_decode($response->getContent(), true);


        $document_id = $content['data']['id'];

        $doc = \Net7\Documents\Document::find($document_id);

        $this->assertEquals($doc->id, $document_id);
        // print_r ($response->getContent());
        $this->assertFileExists($doc->getFirstMedia('documents')->getPath());

    }


}
