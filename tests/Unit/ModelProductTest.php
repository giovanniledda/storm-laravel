<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

class ModelProductTest extends TestCase
{
    /**
     * @return void
     */
    public function testSimpleCreation()
    {
        $data = Product::getSemiFakeData($this->faker);
        $except = [];

        $product1 = Product::factory()->create();
        $this->checkAllFields($product1, $data, $except);

        $product2 = Product::create($data);
        $this->checkAllFields($product2, $data, $except);

        $product3 = Product::createSemiFake($this->faker);
        $this->checkAllFields($product3, $data, $except);

        $this->assertCount(3, Product::all());
    }
}
