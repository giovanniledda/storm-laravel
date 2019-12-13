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
use App\Zone;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Storage;
use StormUtils;
use User;
use function factory;

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

        $faker = $this->faker ? $this->faker : Faker::create();
        $email = StormUtils::getFakeStormEmail($role_name);

        // Register the new user or whatever.
        $password = $role_name;
        $user = User::create([
            'name' => $this->faker->boolean(30) ? $this->faker->firstNameMale : $this->faker->firstNameFemale,
            'surname' => $this->faker->lastName,
            'email' => $email,
            'password' => $password,
            'is_storm' => $this->faker->boolean(30),
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

    public function createProfession($slug): Profession
    {
//        return factory(Profession::class)->create();
        return Profession::createSemiFake($this->faker, $slug);
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

    /**
     * @param Task $task
     * @param string $filepath
     * @param string|null $type
     */
    public function addImageToTask(Task $task, string $filepath, string $type = null)
    {
//        return;
        if (Storage::disk('local-seeder')->exists($filepath)) {
            $task->addDamageReportPhoto($filepath, $type);
        }
    }

    /**
     * @param Boat $boat
     * @param string $filepath
     * @param string|null $type
     */
    public function addImageToBoat(Boat $boat, string $filepath, string $type = null)
    {
//        return;
        if (Storage::disk('local-seeder')->exists($filepath)) {
            $boat->addMainPhoto($filepath, $type);
        }
    }

    /**
     * @param Section $section
     * @param string $filepath
     * @param string|null $type
     */
    public function addImageToSection(Section $section, string $filepath, string $type = null)
    {
//        return;
        if (Storage::disk('local-seeder')->exists($filepath)) {
            $section->addImagePhoto($filepath, $type);
        }
    }

    /**
     * @param Project $project
     * @param int $fathers
     * @param int $children
     */
    public function addFakeZonesToProject(Project $project, int $fathers, int $children)
    {
        if ($project->zones()->count() == 0) {
            for ($i = 1; $i <= $fathers; $i++) {
                $father_zone = factory(Zone::class)->create([
                    'project_id' => $project->id,
                    'code' => $i,
                ]);
                $alhpabet = range('A', 'Z');
                for ($c = 1; $c <= $children; $c++) {
                    $child_zone = factory(Zone::class)->create([
                        'code' => $this->faker->regexify("[$i][{$alhpabet[$c - 1]}]{1}"),
                        'project_id' => $project->id,
                        'parent_zone_id' => $father_zone->id,
                    ]);
                }
            }
        }
    }

    /**
     * Shows how many memory the script is using
     */
    public function print_mem()
    {
        /* Currently used memory */
        $mem_usage = memory_get_usage();

        /* Peak memory usage */
        $mem_peak = memory_get_peak_usage();

        echo 'The script is now using: -' . round($mem_usage / 1024) . "KB- of memory. \n";
        echo 'Peak usage: -' . round($mem_peak / 1024) . "KB- of memory.\n\n";
    }
}
