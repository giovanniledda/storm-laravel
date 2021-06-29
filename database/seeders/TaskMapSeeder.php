<?php

/*
 * by @reina
 */

namespace Database\Seeders;

use App\Task;
use Illuminate\Database\Seeder;

class TaskMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = $this->command->choice('What kind of Tasks are you looking for?', ['All', 'Primary', 'Remarks'], 0);
        if ($type == 'All') {
            $tasks = Task::all();
        } elseif ($type == 'Primary') {
            $tasks = Task::primary()->get();
        } else {
            $tasks = Task::remark()->get();
        }

        $tasks_num = count($tasks);
        $this->command->info("Starting image creation for $tasks_num tasks!");
        $tasks->each(function ($task) { // foreach($posts as $post) { }
            echo PHP_EOL;
            echo 'Try to create map for task #'.$task->id;
            echo PHP_EOL;
            $r = $task->updateMap();
            if (isset($r['success']) && $r['success']) {
                $this->command->info('Map Created');
            } else {
                $this->command->error($r['error']);
            }
        });
    }
}
