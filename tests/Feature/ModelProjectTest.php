<?php

namespace Tests\Feature;

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
        /** @var Project $project */
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

}
