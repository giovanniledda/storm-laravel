<?php

namespace Tests\Unit;

use App\Document;
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


}
