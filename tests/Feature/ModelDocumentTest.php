<?php

namespace Tests\Unit;

use Net7\Documents\Document;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ModelDocumentTest extends TestCase
{


    /**
     * A basic unit test example.
     *
     * @return void
     */

     public function test_can_create_document(){
        // $this->disableExceptionHandling();

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
        $this->assertDatabaseHas('net7_documents', ['title' =>  $fake_title] );

        $this->assertEquals(1, $doc->getMedia('documents')->count());

        // $this->assertFileExists($doc->getFirstMedia('media')->getPath());

        // Assert the file was stored...
        $this->assertFileExists($doc->getFirstMedia('documents')->getPath());

        // Assert a file does not exist...
        // Storage::disk('local')->assertMissing ($nonExistingFilename);
        // $this->assertNotEquals([], Storage::allFiles());

     }

     public function test_revisionable_document(){

        $this->disableExceptionHandling();


        $filename = 'testDocument.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $tempFilepath = '/tmp/'.$filename;
        copy ($filepath, $tempFilepath);

        // we create an UploadedFile and use it to create the document

        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile( $tempFilepath, $filename, null ,null, true);
        $doc = new Document([
            'title' => 'a document',
            'file' => $file,
            'document_number' => 'ISO_9921'

        ]);

        $doc->save();

        $firstMedia = $doc->getRelatedMedia();


        $expected_media_id = 1;

        $this->assertEquals($expected_media_id, $firstMedia->id);
        $this->assertEquals($expected_media_id, $doc->current_media_id);


        // we create another UploadedFile, and use it to update the file related to the document, to check if revisions work
        $filename = 'testDocument2.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $tempFilepath = '/tmp/'.$filename;
        copy ($filepath, $tempFilepath);

        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile( $tempFilepath, $filename, null ,null, true);

        $doc->addUploadedFile($file);
        $doc->refresh(); // much important!

        $expected_media_id = 2;
        $doc->save();

        $secondMedia = $doc->getRelatedMedia();

        $this->assertEquals($expected_media_id, $secondMedia->id);
        $this->assertEquals($expected_media_id, $doc->current_media_id);


        // we create another UploadedFile, and use it to update the file related to the document, to check if revisions work
        $filename = 'testDocument.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $tempFilepath = '/tmp/'.$filename;
        copy ($filepath, $tempFilepath);

        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile( $tempFilepath, $filename, null ,null, true);

        $doc->addUploadedFile($file);
        $doc->refresh(); // much important!

        $expected_media_id = 3;
        $doc->save();

        $thirdMedia = $doc->getRelatedMedia();

        $this->assertEquals($expected_media_id, $thirdMedia->id);
        $this->assertEquals($expected_media_id, $doc->current_media_id);


        $revisions = reset($doc->revisionHistory);

        $this->assertEquals($firstMedia->id, $revisions[0]->old_value);

        $firstHistoryMedia = \Spatie\MediaLibrary\Models\Media::Find( $revisions[0]->old_value);
        $this->assertEquals($firstMedia,  $firstHistoryMedia);

        $this->assertEquals($secondMedia->id, $revisions[0]->new_value);
        $this->assertEquals($secondMedia->id, $revisions[1]->old_value);

        $secondHistoryMedia = \Spatie\MediaLibrary\Models\Media::Find( $revisions[1]->old_value);
        $this->assertEquals($secondMedia,  $secondHistoryMedia);

        $this->assertEquals($thirdMedia->id, $revisions[1]->new_value);

        $thirdHistoryMedia = \Spatie\MediaLibrary\Models\Media::Find( $revisions[1]->new_value);
        $this->assertEquals($thirdMedia,  $thirdHistoryMedia);




        // alternatively you can cycle through revistions as below
        //
        // foreach($doc->revisionHistory as $history){
        //  //...
        // }



     }


    public function test_revisionable_document_on_task(){

        $this->disableExceptionHandling();


        $filename = 'testDocument.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $tempFilepath = '/tmp/'.$filename;
        copy ($filepath, $tempFilepath);
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile( $tempFilepath, $filename, null ,null, true);
        $doc = new Document([
            'title' => 'a document',
            'file' => $file,
            'document_number' => 'ISO_9921'

        ]);
        // $fake_name = $this->faker->sentence;
        // $task = new \App\Task([
        //         'title' => $fake_name,
        //         'description' => $this->faker->text,
        //     ]
        // );
        // $task->save();

        $task = factory( \App\Task::class)->create();
        $project = factory(\App\Project::class)->create();

//        $project->tasks()->save($task)->save(); NOTA: se faccio questo, poi non posso fare $task->project ..mi dice che è nullo
        $task->project()->associate($project)->save();



        $task->addDocumentWithType($doc, 'generic_document');

        $task->refresh();

        $doc = $task->generic_documents->last();
        $firstMedia = $doc->getRelatedMedia();
        $expected_media_id = 1;
        $this->assertEquals($expected_media_id, $firstMedia->id);
        $this->assertEquals($expected_media_id, $doc->current_media_id);



        $filename = 'testDocument2.txt';
        $filepath = __DIR__ . '/'.  $filename;
        $tempFilepath = '/tmp/'.$filename;
        copy ($filepath, $tempFilepath);
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile( $tempFilepath, $filename, null ,null, true);

        $document = $task->generic_documents->last();

        $task->updateDocument($document, $file);
        $task->refresh();

        $doc = $task->generic_documents->last();
        $secondMedia = $doc->getRelatedMedia();
        $expected_media_id = 2;
        $this->assertEquals($expected_media_id, $secondMedia->id);
        $this->assertEquals($expected_media_id, $doc->current_media_id);



    }
}
