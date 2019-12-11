<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ApplicationLogSection;

class ModelApplicationLogSectionTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = ApplicationLogSection::getSemiFakeData($this->faker);
        $except = [];

        $application_log_section1 = factory(ApplicationLogSection::class)->create();
        $this->checkAllFields($application_log_section1, $data, $except);

        $application_log_section2 = ApplicationLogSection::create($data);
        $this->checkAllFields($application_log_section2, $data, $except);

        $application_log_section3 = ApplicationLogSection::createSemiFake($this->faker);
        $this->checkAllFields($application_log_section3, $data, $except);

        $this->assertCount(3, ApplicationLogSection::all());
    }
}
