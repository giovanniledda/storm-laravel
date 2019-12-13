<?php

namespace Tests\Feature;

use function factory;
use App\GenericDataInfoBlock;
use Tests\TestCase;
use App\ApplicationLogSection;

class ModelGenericDataIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {

        /** application_log_section **/
        /** $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null') **/

        $app_log_section = factory(ApplicationLogSection::class)->create();
        $generic_data_info_blocks_num = $this->faker->numberBetween(10, 50);
        $generic_data_info_blocks = factory(GenericDataInfoBlock::class, $generic_data_info_blocks_num)->create();

        // assegno i generic data info blocks (n ($generic_data_info_blocks_num) elementi) all'app log section
        $app_log_section->generic_data_info_blocks()->saveMany($generic_data_info_blocks);

        $this->assertEquals($generic_data_info_blocks_num, $app_log_section->generic_data_info_blocks()->count());

        foreach ($generic_data_info_blocks as $generic_data_info_block) {
            $this->assertEquals($generic_data_info_block->application_log_section->id, $app_log_section->id);
        }
    }
}
