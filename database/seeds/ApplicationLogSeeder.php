<?php

use App\ApplicationLog;
use App\ApplicationLogSection;
use App\Project;
use Illuminate\Database\Seeder;
use Seeds\SeederUtils as Utils;
use Faker\Factory as Faker;

class ApplicationLogSeeder extends Seeder
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
     * @throws Exception
     */
    public function run()
    {

        $this->utils = new Utils();
//        $this->faker = Faker::create();

        $projects = Project::all();
        /** @var Project $project */
        foreach ($projects as $project) {
            if ($this->command->confirm("Proceed with build for Project [ID:{$project->id}]? [y|N]", false)) {
                // Add fake zones
                $this->utils->addFakeZonesToProject($project, 4, 5);

                // Add fake products
                $this->utils->addFakeProductsToProject($project, 4, true);

                // Add fake tools
                $this->utils->addFakeToolsToProject($project, 4, true);

                // WARNING: this remove all application logs
                if ($this->command->confirm("Do you wish to erase all Application Logs for Project [ID:{$project->id}] before creation? [y|N]", false)) {
                    if ($project->application_logs()->count()) {
                        /** @var ApplicationLog $application_log */
                        foreach ($project->application_logs as $application_log) {
                            /** @var ApplicationLogSection $application_log_section */
                            foreach ($application_log->application_log_sections as $application_log_section) {
                                $application_log_section->detections_info_blocks()->delete();
                                $application_log_section->generic_data_info_blocks()->delete();
                                $application_log_section->product_use_info_blocks()->delete();
                                $application_log_section->zone_analysis_info_blocks()->delete();
                                $application_log_section->delete();
                            }
                            $application_log->delete();
                        }
                    }
                }

                // Adda fake application logs
    //            $app_logs = $this->utils->addRandomFakeApplicationLogsToProject($project, 4);
//                $app_logs = $this->utils->addCompleteListFakeApplicationLogsToProject($project);
//                foreach ($app_logs as $app_log) {
//                    $this->utils->addFakeStructureToApplicationLog($app_log);
//                }
            }

        }
    }
}
