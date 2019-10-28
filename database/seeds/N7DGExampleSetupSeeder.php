<?php

use App\Boat;
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

        $this->command->warn(" ------ MANAGE & ASSOCIATE TEMPLATE TO MODEL (Boat) --------");

        $boat = Boat::find(1);
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

    }
}
