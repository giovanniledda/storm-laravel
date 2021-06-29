<?php

namespace Tests\Unit;

use App\DetectionsInfoBlock;
use Tests\TestCase;

class ModelDetectionsIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = DetectionsInfoBlock::getSemiFakeData($this->faker);
        $except = [];

        $detections_ib1 = DetectionsInfoBlock::factory()->create();
        $this->checkAllFields($detections_ib1, $data, $except);

        $detections_ib2 = DetectionsInfoBlock::create($data);
        $this->checkAllFields($detections_ib2, $data, $except);

        $detections_ib3 = DetectionsInfoBlock::createSemiFake($this->faker);
        $this->checkAllFields($detections_ib3, $data, $except);

        $this->assertCount(3, DetectionsInfoBlock::all());
    }
}
