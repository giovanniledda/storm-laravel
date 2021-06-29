<?php

namespace Tests\Feature;

use App\ApplicationLog;
use App\ApplicationLogSection;
use function factory;
use Tests\TestCase;

class ModelApplicationLogSectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {
        $application_log_sections_num = $this->faker->numberBetween(1, 15);

        $application_log = ApplicationLog::factory()->create();
        $application_log_sections = ApplicationLogSection::factory()->count($application_log_sections_num)->create();

        /** application_log **/
        /** $table->foreign('application_log_id')->references('id')->on('application_logs')->onDelete('set null'); **/

        // assegno le app log sections (5 elementi) all'app log
        $application_log->application_log_sections()->saveMany($application_log_sections);

        $this->assertEquals($application_log_sections_num, $application_log->application_log_sections()->count());

        foreach ($application_log_sections as $application_log_section) {
            $this->assertEquals($application_log_section->application_log_id, $application_log->id);
            $this->assertEquals($application_log_section->application_log->id, $application_log->id); // testo la relazione inversa
        }
    }
}
