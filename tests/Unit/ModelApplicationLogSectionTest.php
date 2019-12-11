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

        $application_log_section_ib1 = factory(ApplicationLogSection::class)->create();
        $this->checkAllFields($application_log_section_ib1, $data, $except);

        $application_log_section_ib2 = ApplicationLogSection::create($data);
        $this->checkAllFields($application_log_section_ib2, $data, $except);

        $application_log_section_ib3 = ApplicationLogSection::createSemiFake($this->faker);
        $this->checkAllFields($application_log_section_ib3, $data, $except);

        $this->assertCount(3, ApplicationLogSection::all());
    }
}
