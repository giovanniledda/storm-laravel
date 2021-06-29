<?php

namespace Tests\Unit;

use App\Models\ZoneAnalysisInfoBlock;
use Tests\TestCase;

class ModelZoneAnalysisIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = ZoneAnalysisInfoBlock::getSemiFakeData($this->faker);
        $except = [];

        $zone_analysis_ib1 = ZoneAnalysisInfoBlock::factory()->create();
        $this->checkAllFields($zone_analysis_ib1, $data, $except);

        $zone_analysis_ib2 = ZoneAnalysisInfoBlock::create($data);
        $this->checkAllFields($zone_analysis_ib2, $data, $except);

        $zone_analysis_ib3 = ZoneAnalysisInfoBlock::createSemiFake($this->faker);
        $this->checkAllFields($zone_analysis_ib3, $data, $except);

        $this->assertCount(3, ZoneAnalysisInfoBlock::all());
    }
}
