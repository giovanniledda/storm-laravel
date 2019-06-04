<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DocumentTest extends TestCase
{


    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_create_document()
    {
        $this->assertTrue(true);


        $data = [
            'title' => $this->faker->sentence,
        ];
        $this->post(route('documents.create'), $data)
            ->assertStatus(201)
            ->assertJson($data);

    }
}
