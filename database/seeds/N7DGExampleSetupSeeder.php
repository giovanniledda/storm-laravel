<?php

use App\Boat;
use App\Project;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Seeds\SeederUtils;

class N7DGExampleSetupSeeder extends Seeder
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


        $boat = Boat::find(1);

/*

     // Template: Boat
        $this->command->warn(" ------ MANAGE & ASSOCIATE TEMPLATE TO MODEL (Boat) --------");
        $category = $boat->persistAndAssignTemplateCategory('Boat');
        $placeholders = [
            '${boat_name}' => 'name',
            '${boat_reg_num}' => 'registration_number',
            '${boat_type}' => 'boat_type',
            '${boat_float}' => 'flag',
            '${img_BoatImage:150:150:false}' => 'getMainPhotoPath()',
            '${row_tableOne}' => 'getAllProjectsTableRowInfo()',
        ];
        $boat->insertPlaceholders('Boat', $placeholders, true);
//        $user1->updateTemplateDirPath('User', null);  // prende il default (/storage/app/docs-generator/...)

*/

/*
        // Template: StormBoatTasks
        $this->command->warn(" ------ MANAGE & ASSOCIATE TEMPLATE TO MODEL (StormBoatTasks) --------");
        $category = $boat->persistAndAssignTemplateCategory('StormBoatTasks');
        $placeholders = [
            '${boat_name}' => 'name',
            '${boat_reg_num}' => 'registration_number',
            '${boat_type}' => 'boat_type',
            '${boat_float}' => 'flag',
            '${img_BoatImage:150:150:false}' => 'getMainPhotoPath()',
            '${blC_bloccoTask}' => 'getBloccoTaskInfoArray()',
            '${img_currentTask_img1}' => 'getCurrentTaskImg1()',
            '${img_currentTask_img2}' => 'getCurrentTaskImg2()',
            '${img_currentTask_img3}' => 'getCurrentTaskImg3()',
            '${img_currentTask_img4}' => 'getCurrentTaskImg4()',
            '${img_currentTask_img5}' => 'getCurrentTaskImg5()',
        ];
        $boat->insertPlaceholders('StormBoatTasks', $placeholders, true);

*/

/*
        // Template: StormBoatTasks
        $this->command->warn(" ------ MANAGE & ASSOCIATE TEMPLATE TO MODEL (SampleReport) --------");
        $category = $boat->persistAndAssignTemplateCategory('SampleReport');
        $placeholders = [
            '${boat_name}' => 'name',
            '${boat_reg_num}' => 'registration_number',
            '${boat_type}' => 'boat_type',
            '${img_BoatImage:250:250:false}' => 'getMainPhotoPath()',
            '${date}' => 'printDocxTodayDate()',
            '${blC_bloccoTask}' => 'getBloccoTaskSampleReportInfoArray()',
            '${pageBreak}' => 'printDocxPageBreak()',
            '${img_currentTask_img1}' => 'getCurrentTaskImg1()',
            '${img_currentTask_img2}' => 'getCurrentTaskImg2()',
            '${img_currentTask_img3}' => 'getCurrentTaskImg3()',
            '${img_currentTask_img4}' => 'getCurrentTaskImg4()',
            '${img_currentTask_img5}' => 'getCurrentTaskImg5()',
        ];
        $boat->insertPlaceholders('SampleReport', $placeholders, true);
//        $user1->updateTemplateDirPath('User', null);  // prende il default (/storage/app/docs-generator/...)

*/

        $project = Project::find(1);
        $this->command->warn(" ------ MANAGE & ASSOCIATE TEMPLATE TO PROJECT (corrosion_map) --------");
        $category = $project->persistAndAssignTemplateCategory('corrosion_map');
        $placeholders = [
            '${boat_name}' => 'getBoatName()',
            '${boat_reg_num}' => 'getBoatRegistrationNumber()',
            '${boat_type}' => 'getBoatType()',
            '${img_BoatImage:250:250:false}' => 'getBoatMainPhotoPath()',
            '${date}' => 'printDocxTodayDate()',
            '${blC_bloccoTask}' => 'getBloccoTaskSampleReportInfoArray()',
            '${pageBreak}' => 'printDocxPageBreak()',
            '${img_currentTask_brPos:450:450:false}' => 'getCurrentTaskBridgeImage()',
            '${img_currentTask_img1}' => 'getCurrentTaskImg1()',
            '${img_currentTask_img2}' => 'getCurrentTaskImg2()',
            '${img_currentTask_img3}' => 'getCurrentTaskImg3()',
            '${img_currentTask_img4}' => 'getCurrentTaskImg4()',
            '${img_currentTask_img5}' => 'getCurrentTaskImg5()',
        ];
        $project->insertPlaceholders('corrosion_map', $placeholders, true);

    }
}
