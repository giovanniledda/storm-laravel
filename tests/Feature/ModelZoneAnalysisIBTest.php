<?php

namespace Tests\Feature;

use function factory;
use App\Zone;
use App\ZoneAnalysisInfoBlock;
use Tests\TestCase;
use App\ApplicationLogSection;

class ModelZoneAnalysisIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {

        /** application_log_section **/
        /** $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null') **/

        $zone_analysis_info_blocks_num = $this->faker->numberBetween(10, 50);
        $app_log_section = factory(ApplicationLogSection::class)->create();
        $zone_analysis_info_blocks = factory(ZoneAnalysisInfoBlock::class, $zone_analysis_info_blocks_num)->create();

        // assegno i zone analysis info blocks (n ($zone_analysis_info_blocks_num) elementi) all'app log section
        $app_log_section->zone_analysis_info_blocks()->saveMany($zone_analysis_info_blocks);

        $this->assertEquals($zone_analysis_info_blocks_num, $app_log_section->zone_analysis_info_blocks()->count());

        foreach ($zone_analysis_info_blocks as $zone_analysis_info_block) {
            /** zone **/
            $zone = factory(Zone::class)->create();

            // salvo sia dalla zone che dalla zone analysis info block a seconda del bool
            if ($this->faker->boolean) {
                $zone_analysis_info_block->zone()->save($zone);
            } else {
                $zone->zone_analysis_info_block()->associate($zone_analysis_info_block);
                $zone->save();
            }

            $this->assertEquals($zone_analysis_info_block->zone->id, $zone->id); // testo la relazione inversa
            $this->assertEquals($zone->zone_analysis_info_block->id, $zone_analysis_info_block->id); // testo la relazione inversa
        }


    }
}
