<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class DocumentTest extends TestCase
{


    /**
     * A basic unit test example.
     *
     * @return void
     */

     public function test_can_create_document()
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

        $document = \App\Document::find($document_id);

    }




    public function test_un_test(){
        $response = $this->get('/');

        $response->assertStatus(200);
    }


}
