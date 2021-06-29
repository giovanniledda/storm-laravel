<?php

namespace Tests\Unit;

use App\Models\GenericDataInfoBlock;
use Tests\TestCase;

class ModelGenericDataIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = GenericDataInfoBlock::getSemiFakeData($this->faker);
        $except = [];

        $generic_data_ib1 = GenericDataInfoBlock::factory()->create();
        $this->checkAllFields($generic_data_ib1, $data, $except);

        $generic_data_ib2 = GenericDataInfoBlock::create($data);
        $this->checkAllFields($generic_data_ib2, $data, $except);

        $generic_data_ib3 = GenericDataInfoBlock::createSemiFake($this->faker);
        $this->checkAllFields($generic_data_ib3, $data, $except);

        $this->assertCount(3, GenericDataInfoBlock::all());
    }
}
