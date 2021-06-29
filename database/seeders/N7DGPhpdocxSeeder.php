<?php

namespace Database\Seeders;

use App\MyTemplateProcessor;
use App\Project;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Net7\DocsGenerator\DocsGenerator;
use Seeds\SeederUtils;

// TODO: copiami nel package alla fine!!!!!!!!!!!!!!!!!

class N7DGPhpdocxSeeder extends Seeder
{
    protected $utils;
    protected $faker;
    protected $dg;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $this->utils = new SeederUtils();

        /** @var Project $project */
        $project = Project::find(1);

        $this->command->warn(' ------ MANAGE TEMPLATE (corrosion_map) --------');

        $project->setupCorrosionMapTemplate();

        $this->command->warn(' ------ MANAGE TEMPLATE (environmental_report) --------');

        $project->setupEnvironmentalReportTemplate();
    }
}
