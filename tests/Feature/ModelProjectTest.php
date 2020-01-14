<?php

namespace Tests\Feature;

use App\ApplicationLog;
use App\Product;
use App\Profession;
use App\ProjectUser;
use App\Site;
use App\Project;
use App\Boat;
use function array_map;
use function factory;
use Tests\TestCase;
use Faker\Provider\Base as fakerBase;
use App\User;

class ModelProjectTest extends TestCase
{

    function test_can_create_project_without_site()
    {
        $project = factory(Project::class)->create();
        $this->assertDatabaseHas('projects', ['name' => $project->name]);
    }

    function test_can_create_project_related_to_site()
    {
        $site_name = $this->faker->sentence;
        $site = new Site([
                'name' => $site_name,
                'lat' => $this->faker->randomFloat(2, -60, 60),
                'lng' => $this->faker->randomFloat(2, -60, 60)
            ]
        );
        $site->save();

        $project = factory(Project::class)->create();

        $this->assertDatabaseHas('projects', ['name' => $project->name]);

        $project->site()->associate($site)->save();

        $this->assertEquals($site->name, $project->site->name);

    }

    function test_can_create_project_related_to_boat()
    {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number' => $this->faker->sentence($nbWords = 1),
                'length'  => $this->faker->randomFloat(2, 12, 110),
                'draft'  => $this->faker->randomFloat(2, 2, 15),
                "boat_type"=>"M/Y"
            ]
        );
        $boat->save();

        $project = factory(Project::class)->create();

        $this->assertDatabaseHas('projects', ['name' => $project->name]);

        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->name, $project->boat->name);
    }

    function test_can_clone_project_with_relations()
    {
        $boat_name = $this->faker->sentence;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number' => $this->faker->sentence($nbWords = 1),
                'length'  => $this->faker->randomFloat(2, 12, 110),
                'draft'  => $this->faker->randomFloat(2, 2, 15),
                "boat_type"=>"M/Y"
            ]
        );
        $boat->save();

//        $project = factory(Project::class)->create();
        $project = Project::createSemiFake($this->faker);

        $this->assertDatabaseHas('projects', ['name' => $project->name]);

        $project->boat()->associate($boat)->save();

        $this->assertEquals($boat->name, $project->boat->name);

        $site_name = $this->faker->sentence;
        $site = new Site([
                'name' => $site_name,
                'lat' => $this->faker->randomFloat(2, -60, 60),
                'lng' => $this->faker->randomFloat(2, -60, 60)
            ]
        );
        $site->save();

        $project->site()->associate($site)->save();

        $this->assertEquals($site->name, $project->site->name);

        $newProject = $project->replicate();
        $newProject->save();

        $this->assertEquals($project->acronym, $newProject->acronym);
        $this->assertEquals($project->name, $newProject->name);
        $this->assertEquals($project->start_date, $newProject->start_date);
        $this->assertEquals($project->end_date, $newProject->end_date);

        $this->assertEquals($site->name, $newProject->site->name);
        $this->assertEquals($boat->name, $newProject->boat->name);

        // associo utenti al primo progetto
        $users = factory(User::class, 10)->create();
        foreach ($users as $u) {
            $profession = factory(Profession::class)->create();
            ProjectUser::createOneIfNotExists($u->id, $project->id, $profession->id);
        }

        // li passo al secondo
        $project->transferMyUsersToProject($newProject);

        $this->assertNotEquals(0, $newProject->users()->count());
        $this->assertEquals($newProject->users()->count(), $project->users()->count());
    }

    function test_project_products() {

        /** @var Project $project */
        $project = Project::createSemiFake($this->faker);
        $products = factory(Product::class, 10)->create();

        $project->products()->attach(Product::pluck('id'));
        /** @var Product $product */
        foreach ($products as $product) {
            $this->assertContains($project->id, $product->projects()->pluck('project_id'));
        }

        $prod_ids_for_project = array_map(function($el) {
            return $el['id'];
        }, $project->products->toArray());

        $some_prods = $this->faker->randomElements($products);
        /** @var Product $product */
        foreach ($some_prods as $product) {
            $this->assertContains($product->id, $prod_ids_for_project);
        }

        // faccio lo stesso con un secondo progetto e vedo che i prodotti siano distinti (differenzio per p_type)
        /** @var Project $project2 */
        $project2 = Project::createSemiFake($this->faker);
        $products2 = factory(Product::class, 10)->create([
            'p_type' => 'TEST'
        ]);

        $project2->products()->attach(Product::where('p_type', '=', 'TEST')->pluck('id'));
        foreach ($products2 as $product) {
            $this->assertContains($project2->id, $product->projects()->pluck('project_id'));
            $this->assertNotContains($project->id, $product->projects()->pluck('project_id'));
        }
    }

    function test_project_application_logs() {

        /** @var Project $project */
        $project = Project::createSemiFake($this->faker);
        $application_logs = factory(ApplicationLog::class, 10)->create([
            'project_id' => $project->id
        ]);

//        $project->application_logs()->saveMany($application_logs); // non va, forse perché il campo projecT_id è stato aggiunto postumo (2020_01_02_121545_add_project_to_application_log) come index?
        /** @var ApplicationLog $application_log */
        foreach ($application_logs as $application_log) {
//            $application_log->project()->associate($project);  // non va neanche così, vedi sopra
//            $application_log->save();
            $this->assertEquals($project->id, $application_log->project->id);
        }

        $this->assertEquals(10, $project->application_logs()->count());

        // faccio lo stesso con un secondo progetto e vedo che gli app log siano distinti
        /** @var Project $project2 */
        $project2 = Project::createSemiFake($this->faker);
        $application_logs2 = factory(ApplicationLog::class, 15)->create([
            'project_id' => $project2->id
        ]);

//        $project2->application_logs()->saveMany($application_logs2); // non va, vedi sopra
        foreach ($application_logs2 as $application_log) {
            $this->assertEquals($project2->id, $application_log->project->id);
        }

        $this->assertEquals(15, $project2->application_logs()->count());
    }


    function test_internal_progressive_number() {

        $boats = factory(Boat::class, 3)->create();
        /** @var Boat $boat */
        foreach ($boats as $boat) {
            $projs_index_for_boat = 1;
            $projects = factory(Project::class, 4)->create([
                'boat_id' => $boat->id
            ]);
            /** @var Project $project */
            foreach ($projects as $project) {
//                $project->boat()->associate($boat)->save();
                $this->assertEquals($boat->id, $project->boat->id);
                $this->assertEquals($projs_index_for_boat++, $project->internal_progressive_number);
            }
        }
    }
}
