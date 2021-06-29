<?php

namespace Tests\Feature;

use App\Boat;
use App\Section;
use App\Subsection;
use Tests\TestCase;

class ModelSubsectionTest extends TestCase
{
    // Creo una Boat...
    // ...creo le sue sezioni, random
    // ...ad ogni sezione associo delle sottosezioni
    // ...che al mercato mio padre comprò.

    public function test_can_create_subsections_related_to_sections()
    {
        $boat = Boat::factory()->create();

        $this->assertInstanceOf(Boat::class, $boat);

        $sections = Section::factory()->count($this->faker->randomDigitNotNull)->create();

        foreach ($sections as $section) {
            $section->boat()->associate($boat)->save();  // alternativa a $boat->sections()->saveMany($sections) ? Sembrerebbe di no...

            $this->assertInstanceOf(Section::class, $section);
            $this->assertEquals($section->boat->registration_number, $boat->registration_number);

            $subsections = Subsection::factory()->count($this->faker->randomDigitNotNull)->create();
            $section->subsections()->saveMany($subsections);
        }

//        $boat = Boat::with('sections')->find($boat->id);
        $this->assertNotEquals(count($sections), 0);
        $this->assertEquals(count($boat->sections), count($sections));
        $this->assertNotEquals($boat->sections()->count(), 0);
        $this->assertEquals($boat->sections()->count(), count($sections));

        $total_subsections_num = 0;
        foreach ($boat->sections as $section) {
            $this->assertInstanceOf(Section::class, $section);

            $total_subsections_num += $section->subsections()->count();
        }

        $this->assertEquals($boat->subsections()->count(), $total_subsections_num);
        $this->assertNotEquals($boat->subsections()->count(), 0);
    }
}
