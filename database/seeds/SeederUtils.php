<?php

namespace Seeds;

use App\Boat;
use App\BoatUser;
use App\Profession;
use App\Project;
use App\ProjectSection;
use App\ProjectUser;
use App\Section;
use App\Site;
use App\Subsection;
use App\Task;
use App\TaskInterventType;
use Faker\Factory as Faker;
use StormUtils;
use User;

class SeederUtils
{
    protected $faker;
    
    public function __construct(Faker $faker = null)
    {
        $this->faker = $faker ? $faker : Faker::create();
    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    public function createUser($role_name)
    {

        $faker = Faker::create();
        $email = StormUtils::getFakeStormEmail($role_name);

        // Register the new user or whatever.
        $password = $role_name;
        $user = User::create([
            'name' => $faker->name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->assignRole($role_name);
        return $user;
    }

    public function createSite(): Site
    {
//        return factory(Site::class)->create();
        return Site::createSemiFake($this->faker);
    }

    public function createBoat(): Boat
    {
//        return factory(Boat::class)->create();
        return Boat::createSemiFake($this->faker);
    }

    public function createSection(Boat $boat = null): Section
    {
//        return factory(Site::class)->create();
        return Section::createSemiFake($this->faker, $boat);
    }

    public function createProfession(): Profession
    {
//        return factory(Profession::class)->create();
        return Profession::createSemiFake($this->faker);
    }

    public function createProject(Site $site = null, Boat $boat = null): Project
    {
//        return factory(Project::class)->create();
        return Project::createSemiFake($this->faker, $site, $boat);
    }

    public function createTask(Project $proj = null,
                               Section $sect = null,
                               Subsection $ssect = null,
                               User $author = null,
                               TaskInterventType $type = null): Task
    {
//        return factory(Task::class)->create();
        return Task::createSemiFake($this->faker, $proj, $sect, $ssect, $author, $type);
    }

    public function createTaskInterventType(): TaskInterventType
    {
//        return factory(TaskInterventType::class)->create();
        return TaskInterventType::createSemiFake($this->faker);
    }

    public function associateUserToBoat(User $user, Boat $boat, Profession $profession)
    {
        if (!$boat->hasUserById($user->id)) {
            BoatUser::create(['user_id' => $user->id, 'boat_id' => $boat->id, 'profession_id' => $profession->id]);
        }
    }

    public function associateUserToProject(User $user, Project $project, Profession $profession)
    {
        if (!$project->hasUserById($user->id)) {
            ProjectUser::create(['user_id' => $user->id, 'project_id' => $project->id, 'profession_id' => $profession->id]);
        }
    }

    public function associateSectionToProject(Section $section, Project $project)
    {
        if (!$project->hasSectionById($section->id)) {
            ProjectSection::create(['section_id' => $section->id, 'project_id' => $project->id]);
        }
    }


    public function createTasksAndAssociateWithProject($project = null)
    {
        do {
            try {
                $tasks = factory(Task::class, $this->faker->randomDigitNotNull)->create();
                //        $project->tasks()->saveMany($tasks);  // Vedi mail di Ledda del 24 luglio: se uso questa poi $t->project è null :-(
                $created = true;

            } catch (\Exception $e) {
                $created = false;
            }
        } while (!$created);

        if (isset($tasks)) {
            foreach ($tasks as $t) {
                if ($project) {
                    $t->project()->associate($project)->save();
                }
            }
            return $tasks;
        }

        return [];
    }



    public function createManyProjectsAndAssociateWithSiteAndBoats($site, $boats)
    {
        $all_projects = [];
        if (!empty($boats)) {
            foreach ($boats as $boat) {
                do {
                    try {
                        $projects = factory(Project::class, $this->faker->randomDigitNotNull)->create();
                        $projs_created = true;

                    } catch (\Exception $e) {
                        $projs_created = false;
                    }
                } while (!$projs_created);

                if (isset($projects)) {
                    foreach ($projects as $project) {
                        $project->boat()->associate($boat)->save();
                        $project->site()->associate($site)->save();
                        $all_projects[] = $project;
                    }
                }
            }
        }

        return $all_projects;
    }
}