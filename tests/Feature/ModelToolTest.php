<?php

namespace Tests\Feature;

use function factory;
use App\Tool;
use App\DetectionsInfoBlock;
use Tests\TestCase;

class ModelToolTest extends TestCase
{
    /**
     *
     * @return void
     */
    public function testBasicRelationships()
    {
        /** @var Tool $tool */
        $tool = factory(Tool::class)->create();

        /** @var DetectionsInfoBlock $detections_info_block */
        $detections_info_block = factory(DetectionsInfoBlock::class)->create();

        // salvo dal Detections IB
        $detections_info_block->tool()->associate($tool);
        $detections_info_block->save();

        $this->assertEquals($detections_info_block->tool->id, $tool->id); // testo la relazione inversa
        $this->assertContains($detections_info_block->id, $tool->detections_info_blocks()->pluck('id')) ;

        /** @var Tool $tool2 */
        $tool2 = factory(Tool::class)->create();

        /** @var DetectionsInfoBlock $detections_info_block2 */
        $detections_info_block2 = factory(DetectionsInfoBlock::class)->create();

        // salvo dal Tool
        $tool2->detections_info_blocks()->save($detections_info_block2);
        $tool2->save();

        $this->assertEquals($detections_info_block2->tool->id, $tool2->id); // testo la relazione inversa
        $this->assertContains($detections_info_block2->id, $tool2->detections_info_blocks()->pluck('id')) ;
    }
}
