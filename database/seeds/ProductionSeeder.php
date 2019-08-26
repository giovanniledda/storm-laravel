<?php

use Illuminate\Database\Seeder;
use App\Site;
use App\Profession;
use App\TaskInterventType;
//use Faker\Factory as Faker;

class ProductionSeeder extends Seeder
{
//    protected $faker;

    public function run()
    {
        $this->populateSites();

        $this->populateProfessions();

        $this->populateTaskTypes();
    }


    private function populateSites()
    {

        $this->command->warn("\n\n ________________ Creating Sites ________________\n\n");

        $sites = \Config::get('storm.startup.sites');
        foreach ($sites as $site => $fields) {

            $s = Site::create(Arr::except($fields, ['addresses']));
            $this->command->info("Site {$s->name} [OK]");

            $addresses = $fields['addresses'];
            foreach ($addresses as $addr => $addr_fields) {

                $s->addAddress($addr_fields);
                $this->command->info("Address $addr for Site {$s->name} [OK]");
            }
        }
    }

    private function populateProfessions()
    {

        $this->command->warn("\n\n ________________ Creating Professions ________________\n\n");

        $professions = \Config::get('storm.startup.professions');
        foreach ($professions as $profession => $fields) {
            $p = Profession::create($fields);
            $this->command->info("Profession {$p->name} [OK]");
        }
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

}