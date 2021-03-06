<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ProductUseInfoBlock;

class ModelProductUseIBTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = ProductUseInfoBlock::getSemiFakeData($this->faker);
        $except = [];

        $prod_use_ib1 = factory(ProductUseInfoBlock::class)->create();
        $this->checkAllFields($prod_use_ib1, $data, $except);

        $prod_use_ib2 = ProductUseInfoBlock::create($data);
        $this->checkAllFields($prod_use_ib2, $data, $except);

        $prod_use_ib3 = ProductUseInfoBlock::createSemiFake($this->faker);
        $this->checkAllFields($prod_use_ib3, $data, $except);

        $this->assertCount(3, ProductUseInfoBlock::all());
    }
}
