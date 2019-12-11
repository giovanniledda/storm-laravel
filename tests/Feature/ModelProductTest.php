<?php

namespace Tests\Feature;

use function factory;
use App\Product;
use App\ProductUseInfoBlock;
use Tests\TestCase;
use App\Zone;

class ModelProductTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testBasicRelationships()
    {
        /** product_use_info_block */
        /** $table->foreign('product_use_info_block_id')->references('id')->on('product_use_info_blocks')->onDelete('set null') */

        /** @var Product $product */
        $product = factory(Product::class)->create();

        /** @var ProductUseInfoBlock $product_use_info_block */
        $product_use_info_block = factory(ProductUseInfoBlock::class)->create();

        // salvo dal Prod Use IB
        $product_use_info_block->product()->save($product);

        $this->assertEquals($product_use_info_block->product->id, $product->id); // testo la relazione inversa
        $this->assertEquals($product->product_use_info_block->id, $product_use_info_block->id); // testo la relazione inversa


        /** @var Product $product2 */
        $product2 = factory(Product::class)->create();

        /** @var ProductUseInfoBlock2 $product_use_info_block */
        $product_use_info_block2 = factory(ProductUseInfoBlock::class)->create();

        // salvo dal Product
        $product2->product_use_info_block()->associate($product_use_info_block2);
        $product2->save();

        $this->assertEquals($product_use_info_block2->product->id, $product2->id); // testo la relazione inversa
        $this->assertEquals($product2->product_use_info_block->id, $product_use_info_block2->id); // testo la relazione inversa
    }
}
