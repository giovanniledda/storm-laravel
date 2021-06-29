<?php

namespace Tests\Unit;

use App\Models\ApplicationLog;
use Tests\TestCase;

class ModelApplicationLogTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = ApplicationLog::getSemiFakeData($this->faker);
        $except = [];

        $application_log1 = ApplicationLog::factory()->create();
        $this->checkAllFields($application_log1, $data, $except);

        $application_log2 = ApplicationLog::create($data);
        $this->checkAllFields($application_log2, $data, $except);

        $application_log3 = ApplicationLog::createSemiFake($this->faker);
        $this->checkAllFields($application_log3, $data, $except);

        $this->assertCount(3, ApplicationLog::all());
    }
}
