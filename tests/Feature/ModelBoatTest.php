<?php

namespace Tests\Feature;

use App\Boat;
use App\Section;
use App\Subsection;
use Tests\TestCase;

class ModelBoatTest extends TestCase
{

    function test_can_create_boat_related_to_section_and_subsections()
    {
        $boat = factory(Boat::class)->create();
        $this->assertInstanceOf(Boat::class, $boat);

        $sections = factory(Section::class, $this->faker->randomDigitNotNull)->create();
        $boat->sections()->saveMany($sections);

        $all_subsections = [];
        foreach ($sections as $section) {

            $this->assertInstanceOf(Section::class, $section);
            $this->assertEquals($boat->id, $section->boat_id);

            $subsections = factory(Subsection::class, $this->faker->randomDigitNotNull)->create();
            $section->subsections()->saveMany($subsections);

            foreach ($subsections as $subsection) {
                $all_subsections[] = $subsection;
                $this->assertInstanceOf(Subsection::class, $subsection);
                $this->assertEquals($section->id, $subsection->section_id);
            }
        }

        $this->assertCount(count($all_subsections), $boat->subsections);
    }


}
