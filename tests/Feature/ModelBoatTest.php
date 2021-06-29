<?php

namespace Tests\Feature;

use App\Models\Boat;
use App\Models\Profession;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Section;
use App\Models\Subsection;
use App\Models\User;
use function factory;
use const PROJECT_STATUS_CLOSED;
use const PROJECT_STATUS_IN_SITE;
use Tests\TestCase;

class ModelBoatTest extends TestCase
{
    public function test_can_create_boat_related_to_section_and_subsections()
    {
        $boat = Boat::factory()->create();
        $this->assertInstanceOf(Boat::class, $boat);

        $sections = Section::factory()->count($this->faker->randomDigitNotNull)->create();
        $boat->sections()->saveMany($sections);

        $all_subsections = [];
        foreach ($sections as $section) {
            $this->assertInstanceOf(Section::class, $section);
            $this->assertEquals($boat->id, $section->boat_id);

            $subsections = Subsection::factory()->count($this->faker->randomDigitNotNull)->create();
            $section->subsections()->saveMany($subsections);

            foreach ($subsections as $subsection) {
                $all_subsections[] = $subsection;
                $this->assertInstanceOf(Subsection::class, $subsection);
                $this->assertEquals($section->id, $subsection->section_id);
            }
        }

        $this->assertCount(count($all_subsections), $boat->subsections);
    }

    public function test_access_only_my_boats()
    {
        $boats = Boat::factory()->count(3)->create();

        $project = Project::factory()->create([
            'project_status' => PROJECT_STATUS_IN_SITE,
        ]);

        // associo le 3 boat al progetto $project
        foreach ($boats as $boat) {
            $project->boat()->associate($boat)->save();
            $this->assertEquals($boat->name, $project->boat->name);
        }

        // creo un utente e lo associo al progetto $project
        /** @var User $user */
        $user = User::factory()->create();
        $profession = Profession::factory()->create();
        ProjectUser::createOneIfNotExists($user->id, $project->id, $profession->id);

        // faccio lo stesso...con altre 3 barche, un altro progetto $project2 e un altro utente $user2

        $boats2 = Boat::factory()->count(3)->create();

        $project2 = Project::factory()->create([
            'project_status' => PROJECT_STATUS_IN_SITE,
        ]);

        foreach ($boats2 as $boat) {
            $project2->boat()->associate($boat)->save();
            $this->assertEquals($boat->name, $project2->boat->name);
        }

        /** @var User $user2 */
        $user2 = User::factory()->create();
        $profession2 = Profession::factory()->create();
        ProjectUser::createOneIfNotExists($user2->id, $project2->id, $profession2->id);

        // TEST: $user1 non deve vedere le $boats2 e $user2 non deve vedere le $boats

        $user1_boats_ids = $user->boatsOfMyActiveProjects(true);
        foreach ($boats2 as $boat2) {
            $this->assertNotContains($boat2->id, $user1_boats_ids);
        }

        $user2_boats_ids = $user2->boatsOfMyActiveProjects(true);
        foreach ($boats as $boat1) {
            $this->assertNotContains($boat1->id, $user2_boats_ids);
        }
    }
}
