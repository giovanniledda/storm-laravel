<?php

use App\Task;
use Illuminate\Database\Seeder;
use App\User;
use App\Boat;
use App\Section;
use App\Site;
use App\Project;
use App\Profession;
use App\TaskInterventType;
use App\Utils\Utils;
use Faker\Factory as Faker;

class StageSeeder extends Seeder
{
     protected $faker;
    /* 
     */
    public function run()
    {
        $this->faker = Faker::create();
        // popolo la tabella task_intervent_types;
        $this->populateTaskTypes();
        // popolo la tabella professions;
        $this->populateProfessions();
        // creo due utenti con profilo boot_manager
        $user_1 = $this->createUser(ROLE_BOAT_MANAGER);
        // do il permesso all'utente BOAT_MANAGER
        $user_1->givePermissionTo(PERMISSION_BOAT_MANAGER);
         
        $user_2 = $this->createUser(ROLE_BOAT_MANAGER);
        // do il permesso all'utente BOAT_MANAGER
        $user_2->givePermissionTo(PERMISSION_BOAT_MANAGER);
        // creo un sito
        $site = $this->createSite();
        
        // creo 3 barche 
        $boat1 = $this->createBoat($site);
        
        // creo i ponti NO FAKER !!
        $this->createDeck(
                [   
                    'name'=>'Lower Deck', 
                    'section_type'=>'deck', 
                    'position'=>-1,
                    'code'=>'LD',
                    'boat_id' => $boat1->id
                ]); 
        $this->createDeck(
                [   
                    'name'=>'Main Deck', 
                    'section_type'=>'deck', 
                    'position'=>0,
                    'code'=>'MD',
                    'boat_id' => $boat1->id
                ]); 
         $this->createDeck(
                [   
                    'name'=>'Pool Deck', 
                    'section_type'=>'deck', 
                    'position'=>1,
                    'code'=>'MD',
                    'boat_id' => $boat1->id
                ]); 
         $this->createDeck(
                [   
                    'name'=>'Sun Deck', 
                    'section_type'=>'deck', 
                    'position'=>2,
                    'code'=>'SD',
                    'boat_id' => $boat1->id
                ]); 
        
        $boat2 = $this->createBoat($site);
        $this->createDeck(
                [   
                    'name'=>'Deck 1', 
                    'section_type'=>'deck', 
                    'position'=>0,
                    'code'=>'D1',
                    'boat_id' => $boat2->id
                ]); 
        $this->createDeck(
                [   
                    'name'=>'Deck 2', 
                    'section_type'=>'deck', 
                    'position'=>1,
                    'code'=>'D2',
                    'boat_id' => $boat2->id
                ]); 
         $this->createDeck(
                [   
                    'name'=>'Deck 3', 
                    'section_type'=>'deck', 
                    'position'=>2,
                    'code'=>'D3',
                    'boat_id' => $boat2->id
                ]); 
        
        
        $boat3 = $this->createBoat($site);
        
         
        $this->createDeck(
                [   
                    'name'=>'Deck 1', 
                    'section_type'=>'deck', 
                    'position'=>0,
                    'code'=>'D1',
                    'boat_id' => $boat3->id
                ]); 
        $this->createDeck(
                [   
                    'name'=>'Deck 2', 
                    'section_type'=>'deck', 
                    'position'=>1,
                    'code'=>'D2',
                    'boat_id' => $boat3->id
                ]); 
         $this->createDeck(
                [   
                    'name'=>'Deck 3', 
                    'section_type'=>'deck', 
                    'position'=>2,
                    'code'=>'D3',
                    'boat_id' => $boat3->id
                ]); 
         $this->createDeck(
                [   
                    'name'=>'Deck 4', 
                    'section_type'=>'deck', 
                    'position'=>3,
                    'code'=>'D3',
                    'boat_id' => $boat3->id
                ]); 
         // accoppio boat agli utenti e tutti come owner
         $this->boatAssociate($user_1, $boat1, 1);
         $this->boatAssociate($user_1, $boat2, 1);
         $this->boatAssociate($user_2, $boat3, 1);
         
         // creo i relativi progetti
         $this->createProject($site, $boat1);
         $this->createProject($site, $boat2);
         $this->createProject($site, $boat3);
         
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
    }
    
    
    
    private function populateProfessions() {
        $professions = ['owner','chief engineer', 'captain', 'ship\'s boy'];
        foreach ($professions as $profession) {
            $prof = Profession::create(['name'=>$profession]);
            $prof->save();
        }
    }
    
    
    private function createSite() {
        $site_name = $this->faker->sentence;
        $site = new Site([
            'name' => $site_name,
            'lat' => $this->faker->randomDigitNotNull,
            'lng' => $this->faker->randomDigitNotNull,
        ]);
        $site->save();
        
        return $site;
    }
    
    private function createBoat(App\Site $site) {
        
        $boat_name = $this->faker->name;
        $boat = new Boat([
                'name' => $boat_name,
                'registration_number' => $this->faker->randomDigitNotNull,
                'site_id' => $site->id
            ]
        );
        $boat->save(); 
         
        return $boat;
    }
    
    private function createProject($site, $boat)
    {
        $project_name = $this->faker->sentence;
        $project = new Project([
                'name' => $project_name,
                'site_id' => $site->id,
                'project_status'=> PROJECT_STATUS_OPEN
            ]
        );
        $project->save();
        $project->boat()->associate($boat)->save();
         
        return $project;
    }
    
     private function createUser($role_name)
    {

        $faker = Faker::create();
        $email = Utils::getFakeStormEmail($role_name);

        // Register the new user or whatever.
        $password = $role_name;
        $user = User::create([
            'name' => $faker->name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->assignRole($role_name);
        $this->created_users[$password] = $user;
        return $user;
    }
    
    
    private function boatAssociate(User $user, Boat $boat, $id_profession)
    {
        $boat->associatedUsers()
            ->create(
                    [   
                        'profession_id' => $id_profession, 
                        'boat_id' => $boat->id, 
                        'user_id' => $user->id, 
                        ])
            ->save();
    }
}