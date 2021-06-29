<?php

namespace Tests\Unit;

use App\Tool;
use Tests\TestCase;

class ModelToolTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = Tool::getSemiFakeData($this->faker);
        $except = [];

        $tool1 = Tool::factory()->create();
        $this->checkAllFields($tool1, $data, $except);

        $tool2 = Tool::create($data);
        $this->checkAllFields($tool2, $data, $except);

        $tool3 = Tool::createSemiFake($this->faker);
        $this->checkAllFields($tool3, $data, $except);

        $this->assertCount(3, Tool::all());
    }
}
