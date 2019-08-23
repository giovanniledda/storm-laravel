<?php

use App\Task;
use Illuminate\Database\Seeder;
use App\User;
use App\Boat;
use App\Section;
use App\ProjectSections;
use App\Site;
use App\Project;
use App\Profession;
use App\TaskInterventType;
use App\Utils\Utils;
use Faker\Factory as Faker;

class ProductionSeeder extends Seeder
{
     protected $faker;
    /* 
     */
    public function run()
    {
       // TODO: creare uno o più siti di base con relativi indirizzi
       // TODO: creare una o più professioni di base (da associare nei boat_user e project_user)
       // TODO: creare tipi di task (TaskInterventType)
    }
    
    private function populateTaskTypes() {
        
        $this->command->info("Creating intervent types ");
        $intervent_types = ['damaged', 'corrosion', 'other' ];
        foreach ($intervent_types as $intervent) {
            $t = TaskInterventType::create(
                    [
                        'name' => $intervent
                    ]);
            $t->save();
         $this->command->info("$intervent [OK]");
        } 
    }
    
    private function createDeck($deck) {
        $d = Section::create( $deck );
        $d->save();
        return $d;
    }
    
    
    
    private function populateProfessions() {
        $this->command->info("Creating Professions :");
        $professions = ['owner','chief engineer', 'captain', 'ship\'s boy'];
        foreach ($professions as $profession) {
            $prof = Profession::create(['name'=>$profession, 'is_storm'=>0]);
            $prof->save();
            $this->command->info("$profession");
        }
        $this->command->info("Storm Professions :");
        $professions_storm = ['Manager','3d Designer', 'captain', 'ship\'s boy'];
        foreach ($professions_storm as $profession_storm) {
            $prof = Profession::create(['name'=>$profession_storm, 'is_storm'=>1]);
            $this->command->info("$profession_storm");
            $prof->save();
        }
    }

}