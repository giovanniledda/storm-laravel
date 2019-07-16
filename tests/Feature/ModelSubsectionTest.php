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

    function test_can_create_subsection_related_to_section()
    {
        $boat_name = $this->faker->sentence;
        $boat = Boat::create([
                'name' => $boat_name,
                'registration_number' => $this->faker->sentence
            ]
        );

        $sections_types = ['left_side', 'right_side', 'deck'];
        $sections = [];
        $sections[] = Section::create([
                'name' => 'Right side',
                'type' => $this->faker->randomElements($sections_types)
            ]
        );

        $sections[] = Section::create([
                'name' => 'Right side',
                'type' => $this->faker->randomElements($sections_types)
            ]
        );

        $sections[] = Section::create([
                'name' => 'Desk 1',
                'type' => $this->faker->randomElements($sections_types)
            ]
        );

        $sections[] = Section::create([
                'name' => 'Desk 2',
                'type' => $this->faker->randomElements($sections_types)
            ]
        );

        $sections[] = Section::create([
                'name' => 'Desk 3',
                'type' => $this->faker->randomElements($sections_types)
            ]
        );

        foreach ($sections as $section) {
            for ($i = 0; $i < 10; $i++) {
                $sub_sect = Subsection::create([
                        'name' => $this->faker->word,
                    ]
                );
                $sub_sect->section()->associate($section);
                $sub_sect->save();
            }
        }

        $boat->sections()->saveMany($sections);
    }


}
