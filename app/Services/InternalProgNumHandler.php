<?php

namespace App\Services;

use App\Models\Boat;
use App\Models\Project;
use App\Models\Task;

class InternalProgNumHandler
{
    public function run()
    {
        $boats = Boat::all();
        /** @var Boat $boat */
        foreach ($boats as $boat) {
            $projects = $boat->projects()->orderBy('created_at')->get();
            $projs_index_for_boat = 1;
            $tasks_index_for_boat = 1;
            /** @var Project $project */
            foreach ($projects as $project) {
//                $this->command->info("Project ({$project->created_at}) {$project->name} [ID: {$project->id}] for Boat {$boat->name}, Internal Progressive Number: $projs_index_for_boat");
                $project->update(['internal_progressive_number' => $projs_index_for_boat++]);
                $tasks = $project->tasks()->withTrashed()->orderBy('created_at')->get();
                /** @var Task $tasks */
                foreach ($tasks as $task) {
//                    $this->command->info("Task ({$task->created_at}) {$task->name} [ID: {$task->id}] for Boat {$boat->name}, Internal Progressive Number: $tasks_index_for_boat");
                    $task->update(['internal_progressive_number' => $tasks_index_for_boat++]);
                }
            }
        }
    }
}
