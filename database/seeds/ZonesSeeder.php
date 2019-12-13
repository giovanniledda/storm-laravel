<?php

use App\Project;
use Illuminate\Database\Seeder;
use Seeds\SeederUtils as Utils;
use Faker\Factory as Faker;

class ZonesSeeder extends Seeder
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

        // Get all of the projects
        $projects = Project::all();
        /** @var Project $project */
        foreach ($projects as $project) {
            $this->utils->addFakeZonesToProject($project, 4, 5);
        }
    }
}
