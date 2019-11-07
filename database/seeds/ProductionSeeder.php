<?php

use Illuminate\Database\Seeder;
use App\Site;
use App\Profession;
use App\TaskInterventType;
//use Faker\Factory as Faker;
use Seeds\SeederUtils as Utils;


class ProductionSeeder extends Seeder
{
//    protected $faker;
protected $utils;
protected $professions;
    public function run()
    {

        $this->utils = new Utils();
        $this->populateSites();

        $this->professions = $this->populateProfessions();

        $this->populateTaskTypes();
        $this->createUsers();
    }


    private function populateSites()
    {

        $this->command->warn("\n\n ________________ Creating Sites ________________\n\n");


        $site = $this->utils->createSite();

        $site->setName('Cantieri Benetti Livorno')
            ->setLocation('Via Edda Fagni, 1, 57100 – Livorno, Italy')
            ->setLat(43.5430829)
            ->setLng(10.2994663)
            ->save();

        $this->command->info("Site {$site->name} created");


        $site2 = $this->utils->createSite();

        $site2->setName('NCA Refit')
            ->setLocation('Viale Colombo, 4Bis 54033 – Marina di Carrara (MS) Italy')
            ->setLat(43.9763331)
            ->setLng(10.0188368)
            ->save();

        $this->command->info("Site {$site2->name} created");


        // $sites = \Config::get('storm.startup.sites');
        // foreach ($sites as $site => $fields) {

        //     $s = Site::create(Arr::except($fields, ['addresses']));
        //     $this->command->info("Site {$s->name} [OK]");

        //     $addresses = $fields['addresses'];
        //     foreach ($addresses as $addr => $addr_fields) {

        //         $s->addAddress($addr_fields);
        //         $this->command->info("Address $addr for Site {$s->name} [OK]");
        //     }
        // }
    }

    private function populateProfessions()
    {

        $this->command->warn("\n\n ________________ Creating Professions ________________\n\n");


        $professions = [];
        $professions[0] = $this->utils->createProfession('owner');
        for ($s = 1; $s < 20; $s++) {
            $professions[$s] = $this->utils->createProfession('worker');

            $this->command->info("Profession {$professions[$s]->name} created");
        }

        // $professions = \Config::get('storm.startup.professions');
        // foreach ($professions as $profession => $fields) {
        //     $p = Profession::create($fields);
        //     $this->command->info("Profession {$p->name} [OK]");
        // }
        return $professions;
    }

    private function populateTaskTypes()
    {

        $this->command->warn("\n\n ________________ Creating Task Intervent Types ________________\n\n");

        $intervent_types = \Config::get('storm.startup.task_intervent_types');
        foreach ($intervent_types as $task_type => $fields) {
            $tit = TaskInterventType::create($fields);
            $this->command->info("Task Intervent Type {$tit->name} [OK]");
        }

    }

    private function createUsers(){

        $users = [
            [
                'name' => 'Claudio',
                'surname' => 'Mazzuoli',
                'email'   => 'claudio@stormyachts.eu'
            ],  [
                'name' => 'David Andrew',
                'surname' => 'Fryer',
                'email' => 'info@stormyachts.eu',
            ],  [
                'name' => 'Matteo',
                'surname' => 'Gabbriellini',
                'email'  => 'matteo@stormyachts.eu'
            ],  [
                'name' =>  'Francesco' ,
                'surname' => 'Sassano',
                'email'  => 'francesco@stormyachts.eu'
            ],  [
                'name' =>  'Elisa' ,
                'surname' => 'Roberti',
                'email' => 'elisa@stormyachts.eu'
            ]

        ];


        $password = 'password';

        foreach ($users as $u)  {

            $user = User::create([
            'name' => $u['name'],
            'surname' => $u['surname'],
            'email' => $u['email'],
            'password' => $password,
            'is_storm' => true,
        ]);



        $user->assignRole(PERMISSION_ADMIN);
        $user->assignRole(PERMISSION_BOAT_MANAGER);
        $user->assignRole(PERMISSION_BACKEND_MANAGER);
        $user->assignRole(PERMISSION_WORKER);



    }

    }

}
