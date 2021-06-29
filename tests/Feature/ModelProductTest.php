<?php

namespace Tests\Feature;

use App\Product;
use App\ProductUseInfoBlock;
use function factory;
use Tests\TestCase;

class ModelProductTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var ProductUseInfoBlock $product_use_info_block */
        $product_use_info_block = ProductUseInfoBlock::factory()->create();

        // salvo dal Prod Use IB
        $product_use_info_block->product()->associate($product);
        $product_use_info_block->save();

        $this->assertEquals($product_use_info_block->product->id, $product->id); // testo la relazione inversa
        $this->assertContains($product_use_info_block->id, $product->product_use_info_blocks()->pluck('id'));

        /** @var Product $product2 */
        $product2 = Product::factory()->create();

        /** @var ProductUseInfoBlock $product_use_info_block2 */
        $product_use_info_block2 = ProductUseInfoBlock::factory()->create();

        // salvo dal Product
        $product2->product_use_info_blocks()->save($product_use_info_block2);
        $product2->save();

        $this->assertEquals($product_use_info_block2->product->id, $product2->id); // testo la relazione inversa
        $this->assertContains($product_use_info_block2->id, $product2->product_use_info_blocks()->pluck('id'));
    }
}
