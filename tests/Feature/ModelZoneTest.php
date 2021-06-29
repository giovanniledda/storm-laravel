<?php

namespace Tests\Feature;

use App\Project;
use App\Zone;
use App\ZoneAnalysisInfoBlock;
use function factory;
use Tests\TestCase;

class ModelZoneTest extends TestCase
{
    /**
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

        /** children_zones (Zone) */
        /** $table->foreign('parent_zone_id')->references('id')->on('zones')->onDelete('set null'); */
        $this->assertEquals(2, $zone_parent->children_zones()->count());  // testo la relazione inversa

        /** project */
        /** $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null'); */

        /** @var Project $project */
        $project = factory(Project::class)->create();
        $zone_parent->project()->associate($project);
        $zone_parent->save();

        $this->assertEquals($zone_parent->project->id, $project->id);
        $this->assertContains($zone_parent->id, $project->zones()->pluck('id')); // testo la relazione inversa

        // test: countParentZones
        $zone_parent2 = factory(Zone::class)->create();
        $zone_parent2->project()->associate($project);
        $zone_parent2->save();

        $zone_parent3 = factory(Zone::class)->create();
        $zone_parent3->project()->associate($project);
        $zone_parent3->save();

        $this->assertEquals(3, $project->countParentZones());

        // test: countParentZonesByData
        $this->assertEquals(1, $project->countParentZonesByData(
            ['code' => $zone_parent3->code, 'description' => $zone_parent3->description]
        ));

        // testare: countChildrenZonesByData
        $this->assertEquals(1, $project->countChildrenZonesByData(
            $zone_parent->id,
            ['code' => $zone_child1->code, 'description' => $zone_child1->description]
        ));
        $this->assertEquals(1, $project->countChildrenZonesByData(
            $zone_parent->id,
            ['code' => $zone_child2->code, 'description' => $zone_child2->description]
        ));
        $this->assertEquals(0, $project->countChildrenZonesByData(
            $zone_parent3->id,
            ['code' => $zone_child2->code, 'description' => $zone_child2->description]
        ));

        // test su: transferMyZonesToProject
        /** @var Project $project2 */
        $project2 = factory(Project::class)->create();
        $project->transferMyZonesToProject($project2);

        $this->assertEquals(3, $project2->countParentZones());
        $this->assertEquals(1, $project2->countParentZonesByData(
            ['code' => $zone_parent->code, 'description' => $zone_parent->description]
        ));
        $this->assertEquals(1, $project2->countParentZonesByData(
            ['code' => $zone_parent2->code, 'description' => $zone_parent2->description]
        ));
        $this->assertEquals(1, $project2->countParentZonesByData(
            ['code' => $zone_parent3->code, 'description' => $zone_parent3->description]
        ));

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
            $this->assertContains($zone_analysis_info_block->id, $zone_child1->zone_analysis_info_blocks()->pluck('id'));
        }
    }
}
