<?php

namespace Tests\Feature;

use Laravel\Passport\Passport;
use Tests\TestApiCase;

use App\Project;
use App\Boat;
use App\Task;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiCommentTest extends TestApiCase
{
    /** create **/
    function test_can_create_comment_related_to_ask()
    {

        $this->disableExceptionHandling();

    }

}
