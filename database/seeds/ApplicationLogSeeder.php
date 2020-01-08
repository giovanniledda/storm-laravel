<?php

use App\Project;
use Illuminate\Database\Seeder;
use Seeds\SeederUtils as Utils;
use Faker\Factory as Faker;

class ProductsSeeder extends Seeder
{
    /**
     * @var Utils
     */
    private $utils;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * Run the database seeds.
     *
     * @return  void
     */
    public function run()
    {

        $this->utils = new Utils();
//        $this->faker = Faker::create();

        $projects = Project::all();
        /** @var Project $project */
        foreach ($projects as $project) {
            // Add fake zones
            $this->utils->addFakeZonesToProject($project, 4, 5);

            // Add fake products
            $this->utils->addFakeProductsToProject($project, 4);

            // Add fake tools
            $this->utils->addFakeToolsToProject($project, 4);
        }
    }
}
