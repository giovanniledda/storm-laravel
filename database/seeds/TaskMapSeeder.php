<?php

use Illuminate\Database\Seeder;
use App\Task;

class TaskMapSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // seleziono tutti i tasks
        $tasks = Task::all();

        $tasks->each(function($task) // foreach($posts as $post) { }
        {
            echo PHP_EOL;
            echo 'Try to create map for task #'. $task->id;
            echo PHP_EOL;
            $r = $task->updateMap();
        if (isset($r['success']) && $r['success']) {
            $this->command->info("Map Created");
        } else {
            $this->command->error($r['error']);
        }
            
      });
    }

}
