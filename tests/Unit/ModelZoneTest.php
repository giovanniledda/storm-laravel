<?php

namespace Tests\Unit;

use App\Zone;
use Tests\TestCase;

class ModelZoneTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = Zone::getSemiFakeData($this->faker);
        $except = ['project_id', 'parent_zone_id'];

        $zone1 = factory(Zone::class)->create();
        $this->checkAllFields($zone1, $data, $except);

        $zone2 = Zone::create($data);
        $this->checkAllFields($zone2, $data, $except);

        $zone3 = Zone::createSemiFake($this->faker);
        $this->checkAllFields($zone3, $data, $except);

        $this->assertCount(3, Zone::all());
    }
}
