<?php

namespace Tests\Feature;

use App\Models\ApplicationLogSection;
use App\Models\Product;
use App\Models\ProductUseInfoBlock;
use function factory;
use Tests\TestCase;

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
        $app_log_section = ApplicationLogSection::factory()->create();
        $product_use_info_blocks = ProductUseInfoBlock::factory()->count($product_use_info_blocks_num)->create();

        // assegno i product use info blocks (n ($product_use_info_blocks_num) elementi) all'app log section
        $app_log_section->product_use_info_blocks()->saveMany($product_use_info_blocks);

        $this->assertEquals($product_use_info_blocks_num, $app_log_section->product_use_info_blocks()->count());

        /** @var ProductUseInfoBlock $product_use_info_block */
        foreach ($product_use_info_blocks as $product_use_info_block) {
            $this->assertEquals($product_use_info_block->application_log_section->id, $app_log_section->id);

            /** @var Product $product */
            $product = Product::factory()->create();

            // salvo sia dai product che dal product use info block a seconda del bool
            if ($this->faker->boolean) {
                $product_use_info_block->product()->associate($product);
                $product_use_info_block->save();
            } else {
                $product->product_use_info_blocks()->save($product_use_info_block);
            }

            $this->assertEquals($product_use_info_block->product->id, $product->id); // testo la relazione inversa
            $this->assertContains($product_use_info_block->id, $product->product_use_info_blocks()->pluck('id'));
        }
    }
}
