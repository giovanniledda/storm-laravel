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

        $app_log_section = factory(ApplicationLogSection::class)->create();
        $zone_analysis_info_blocks_num = $this->faker->numberBetween(10, 50);
        $zone_analysis_info_blocks = factory(ZoneAnalysisInfoBlock::class, $zone_analysis_info_blocks_num)->create();

        // assegno i zone analysis info blocks (n ($zone_analysis_info_blocks_num) elementi) all'app log section
        $app_log_section->zone_analysis_info_blocks()->saveMany($zone_analysis_info_blocks);

        $this->assertEquals($zone_analysis_info_blocks_num, $app_log_section->zone_analysis_info_blocks()->count());

        /** @var ZoneAnalysisInfoBlock $zone_analysis_info_block */
        foreach ($zone_analysis_info_blocks as $zone_analysis_info_block) {

            $this->assertEquals($zone_analysis_info_block->application_log_section->id, $app_log_section->id);

            /** @var Zone $zone */
            $zone = factory(Zone::class)->create();

            // salvo sia dalla zone che dalla zone analysis info block a seconda del bool
            if ($this->faker->boolean) {
                $zone_analysis_info_block->zone()->associate($zone);
                $zone_analysis_info_block->save();
            } else {
                $zone->zone_analysis_info_blocks()->save($zone_analysis_info_block);
            }

            $this->assertEquals($zone_analysis_info_block->zone->id, $zone->id); // testo la relazione inversa
            $this->assertContains($zone_analysis_info_block->id, $zone->zone_analysis_info_blocks()->pluck('id')) ;
        }
    }
}
