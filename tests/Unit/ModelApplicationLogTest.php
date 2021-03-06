<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ApplicationLog;

class ModelApplicationLogTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = ApplicationLog::getSemiFakeData($this->faker);
        $except = [];

        $application_log1 = factory(ApplicationLog::class)->create();
        $this->checkAllFields($application_log1, $data, $except);

        $application_log2 = ApplicationLog::create($data);
        $this->checkAllFields($application_log2, $data, $except);

        $application_log3 = ApplicationLog::createSemiFake($this->faker);
        $this->checkAllFields($application_log3, $data, $except);

        $this->assertCount(3, ApplicationLog::all());
    }
}
