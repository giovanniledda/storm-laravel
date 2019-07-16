<?php

namespace Tests\Feature;

use App\Boat;
use App\Section;
use App\Subsection;
use App\Task;
use App\Project;
use Tests\TestCase;

class ModelSubsectionTest extends TestCase
{

    // Creo una Boat...
    // ...creo le sue sezioni, random
    // ...ad ogni sezione associo delle sottosezioni
    // ...che al mercato mio padre comprò.

    function test_can_create_subsections_related_to_sections()
    {
        $boat = factory(Boat::class)->make();

        $this->assertInstanceOf(Boat::class, $boat);

        $sections = factory(Section::class, $this->faker->randomDigitNotNull)->make();

//        $boat->sections()->saveMany($sections);  // se faccio questo, non le associa alla boat

        foreach ($sections as $section) {

            $section->boat()->associate($boat);  // alternativa a $boat->sections()->saveMany($sections) ? Sembrerebbe di no...
            $section->save();  // alternativa a $boat->sections()->saveMany($sections) ? Sembrerebbe di no...

            $this->assertInstanceOf(Section::class, $section);
            $this->assertEquals($section->boat->registration_number, $boat->registration_number);

            $subsections = factory(Subsection::class, $this->faker->randomDigitNotNull)->make();
//            $section->subsections()->saveMany($subsections);

            foreach ($subsections as $subsection) {

                $subsection->section()->associate($section);
                $subsection->save();
            }
        }

        $this->assertNotEquals(count($sections), 0);
        $this->assertEquals(count($boat->sections), count($sections));
        $this->assertNotEquals($boat->sections()->count(), 0);
        $this->assertEquals($boat->sections()->count(), count($sections));

        $total_subsections_num = 0;
        foreach ($boat->sections as $section) {

            $this->assertInstanceOf(Section::class, $section);

            $total_subsections_num += $section->subsections()->count();
        }

//        $this->assertEquals($boat->subsections()->count(), $total_subsections_num);
//        $this->assertNotEquals($boat->subsections()->count(), 0);

    }


}
