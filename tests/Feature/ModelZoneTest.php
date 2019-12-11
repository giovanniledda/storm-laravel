<?php

namespace Tests\Feature;

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
        $zone_parent = factory(Zone::class)->create();
        $zone_child1 = factory(Zone::class)->create();
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
    }
}
