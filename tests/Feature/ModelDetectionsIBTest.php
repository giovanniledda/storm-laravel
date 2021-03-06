<?php

namespace Tests\Feature;

use function factory;
use App\DetectionsInfoBlock;
use Tests\TestCase;
use App\ApplicationLogSection;

class ModelDetectionsIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {
        /** application_log_section **/
        /** $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null') **/

        /** @var ApplicationLogSection $app_log_section */
        $app_log_section = factory(ApplicationLogSection::class)->create();
        $detections_info_blocks_num = $this->faker->numberBetween(10, 50);
        $detections_info_blocks = factory(DetectionsInfoBlock::class, $detections_info_blocks_num)->create();

        // assegno i generic data info blocks (n ($detections_info_blocks_num) elementi) all'app log section
        $app_log_section->detections_info_blocks()->saveMany($detections_info_blocks);

        $this->assertEquals($detections_info_blocks_num, $app_log_section->detections_info_blocks()->count());

        foreach ($detections_info_blocks as $generic_data_info_block) {
            $this->assertEquals($generic_data_info_block->application_log_section->id, $app_log_section->id);
        }
    }
}
