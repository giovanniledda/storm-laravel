<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Tool;

class ModelToolTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testSimpleCreation()
    {

        $data = Tool::getSemiFakeData($this->faker);
        $except = [];

        $tool1 = factory(Tool::class)->create();
        $this->checkAllFields($tool1, $data, $except);

        $tool2 = Tool::create($data);
        $this->checkAllFields($tool2, $data, $except);

        $tool3 = Tool::createSemiFake($this->faker);
        $this->checkAllFields($tool3, $data, $except);

        $this->assertCount(3, Tool::all());
    }
}
