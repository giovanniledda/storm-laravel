<?php

namespace Tests\Feature;

use function factory;
use App\Product;
use App\ProductUseInfoBlock;
use Tests\TestCase;
use App\ApplicationLogSection;

class ModelProductUseIBTest extends TestCase
{
    /**
     * @return void
     */
    public function testBasicRelationships()
    {

        /** application_log_section **/
        /** $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null') **/

        $product_use_info_blocks_num = $this->faker->numberBetween(10, 50);
        $app_log_section = factory(ApplicationLogSection::class)->create();
        $product_use_info_blocks = factory(ProductUseInfoBlock::class, $product_use_info_blocks_num)->create();

        // assegno i product use info blocks (n ($product_use_info_blocks_num) elementi) all'app log section
        $app_log_section->product_use_info_blocks()->saveMany($product_use_info_blocks);

        $this->assertEquals($product_use_info_blocks_num, $app_log_section->product_use_info_blocks()->count());

        foreach ($product_use_info_blocks as $product_use_info_block) {
            /** products **/
            $product = factory(Product::class)->create();

            // salvo sia dai product che dal product use info block a seconda del bool
            if ($this->faker->boolean) {
                $product_use_info_block->product()->save($product);
            } else {
                $product->product_use_info_block()->associate($product_use_info_block);
                $product->save();
            }

            $this->assertEquals($product_use_info_block->product->id, $product->id); // testo la relazione inversa
            $this->assertEquals($product->product_use_info_block->id, $product_use_info_block->id); // testo la relazione inversa
        }


    }
}
