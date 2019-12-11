<?php

namespace Tests\Feature;

use App\ZoneAnalysisInfoBlock;
use function factory;
use Tests\TestCase;
use App\Zone;

class ModelZoneTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testBasicRelationships()
    {
        /** @var Zone $zone_parent */
        $zone_parent = factory(Zone::class)->create();
        /** @var Zone $zone_child1 */
        $zone_child1 = factory(Zone::class)->create();
        /** @var Zone $zone_child2 */
        $zone_child2 = factory(Zone::class)->create();

        /** parent_zone (Zone) */
        /** $table->foreign('parent_zone_id')->references('id')->on('zones')->onDelete('set null'); */

        // associo 2 zone ad una zona "padre" o radice

        $zone_child1->parent_zone()->associate($zone_parent);
        $zone_child1->save();

        $zone_child2->parent_zone()->associate($zone_parent);
        $zone_child2->save();

        $this->assertEquals($zone_child1->parent_zone_id, $zone_parent->id);
        $this->assertEquals($zone_child2->parent_zone_id, $zone_parent->id);
        $this->assertEquals(2, $zone_parent->children_zones()->count());  // testo la relazione inversa

        /** zone_analysis_info_block */
        /** $table->foreign('zone_analysis_info_block_id')->references('id')->on('zone_analysis_info_blocks')->onDelete('set null') */

        $zone_analysis_info_blocks_num = $this->faker->numberBetween(10, 50);
        $zone_analysis_info_blocks = factory(ZoneAnalysisInfoBlock::class, $zone_analysis_info_blocks_num)->create();

        /** @var ZoneAnalysisInfoBlock $zone_analysis_info_block */
        foreach ($zone_analysis_info_blocks as $zone_analysis_info_block) {

            if ($this->faker->boolean) {
                $zone_analysis_info_block->zone()->associate($zone_child1);
                $zone_analysis_info_block->save();
            } else {
                $zone_child1->zone_analysis_info_blocks()->save($zone_analysis_info_block);
            }

            $this->assertEquals($zone_analysis_info_block->zone->id, $zone_child1->id); // testo la relazione inversa
            $this->assertContains($zone_analysis_info_block->id, $zone_child1->zone_analysis_info_blocks()->pluck('id')) ;
        }
    }
}
