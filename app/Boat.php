<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Net7\DocsGenerator\Traits\HasDocsGenerator;
use Net7\Documents\Document;
use Net7\Documents\DocumentableTrait;
use const PROJECT_STATUS_CLOSED;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Boat extends Model
{
    use DocumentableTrait, HasDocsGenerator;

    protected $table = 'boats';
    protected $fillable = [
        'name',
        'registration_number',
       // 'site_id',
        'flag',
        'manufacture_year',
        'length',
        'draft',
        'beam',
    ];

    // Usate con il DocsGenerator
    protected $_currentTask;
    protected $_currentTaskPhotos;

    public function getMediaPath($media)
    {
        $document = $media->model;
        $media_id = $media->id;
        $boat_id = $this->id;

        $path = 'boats'.DIRECTORY_SEPARATOR.$boat_id.DIRECTORY_SEPARATOR.$document->type.
               DIRECTORY_SEPARATOR.$media_id.DIRECTORY_SEPARATOR;

        return $path;
    }

    public function site()
    {
        return $this->hasOne('App\Site');
//        return $this->hasOneThrough('App\Site', 'App\Project');  // NON funziona perché i progetti sono "many" e il site è "one"
    }

    public function sections()
    {
        return $this->hasMany('App\Section');
    }

    public function subsections()
    {
        return $this->hasManyThrough('App\Subsection', 'App\Section');
    }

    public function projects()
    {
        return $this->hasMany('App\Project');
    }

    public function projectsRelatedToUser($user_id)
    {
        return $this->projects()
            ->join('project_user', 'project_user.project_id', '=', 'projects.id')
            ->where('project_user.user_id', '=', $user_id)
            ->get();
    }

    public function closedProjects()
    {
        return $this->projects()
            ->where('project_status', '=', PROJECT_STATUS_CLOSED)
            ->orderBy('end_date', 'DESC')
            ->get();
    }

    /**
     * Data array JSONAPI version for closedProjects function
     *
     * @return array
     */
    public function closedProjectsJsonApi()
    {
        $data = ['data' => []];
        $c_projs = $this->closedProjects();
        if (! empty($c_projs)) {
            foreach ($c_projs as $proj) {
                $data['data'][] = [
                    'type' => 'projects',
                    'id' => $proj->id,
                    'attributes' => $proj,
                ];
            }
        }

        return $data;
    }

    public function history()
    {
        return $this->morphMany('App\History', 'historyable');
    }

    public function associatedUsers()
    {
        return $this->hasMany('App\BoatUser');
    }

    // owner ed equipaggio
    public function users()
    {
        return $this->belongsToMany('App\User')
            ->using('App\BoatUser')
            ->withPivot([
                'profession_id',
            ]);
    }

    /**
     * @param int $uid
     *
     * @return BelongsToMany
     */
    public function getUserByIdBaseQuery($uid)
    {
        return $this->users()->where('users.id', '=', $uid);
    }

    /**
     * @param int $uid
     *
     * @return User
     */
    public function getUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->first();
    }

    /**
     * @param int $uid
     *
     * @return bool
     */
    public function hasUserById($uid)
    {
        return $this->getUserByIdBaseQuery($uid)->count() > 0;
    }

    /**
     * Creates a Boat using some fake data and some others that have sense
     *
     * @param Faker $faker
     *
     * @return Boat $boat
     */
    public static function createSemiFake(Faker $faker)
    {
        $boat = new self([
                'name' => $faker->suffix.' '.$faker->name,
                'registration_number' => $faker->randomDigitNotNull,
                'length' => $faker->randomFloat(4, 30, 150),
                'beam' => $faker->randomFloat(4, 5, 22),
                'draft' => $faker->randomFloat(4, 1, 2),
                'boat_type' => $faker->randomElement([BOAT_TYPE_MOTOR, BOAT_TYPE_SAIL]),
                'flag' => $faker->country(),
                'manufacture_year' => $faker->year(),
            ]
        );
        $boat->save();

        return $boat;
    }

    /**
     * Return the user that has "owner" slug on his profession
     *
     * @return mixed
     */
    public function getOwner()
    {
        return self::select('users.name', 'users.surname')
            ->Join('boat_user', 'boat_user.boat_id', '=', 'boats.id')
            ->Join('professions', 'boat_user.profession_id', '=', 'professions.id')
            ->Join('users', 'users.id', '=', 'boat_user.user_id')
            ->where('professions.slug', '=', PROJECT_USER_ROLE_OWNER)
            ->where('boats.id', '=', $this->id)
            ->first();
    }

    public static function boatsWithClosedProjects()
    {
        $boat_ids = Project::closedProjects()->pluck('boat_id');

        return self::whereIn('id', $boat_ids)->get();
    }

    public static function boatsWithActiveProjects()
    {
        $boat_ids = Project::activeProjects()->pluck('boat_id');

        return self::whereIn('id', $boat_ids)->get();
    }

    /**
     * Adds an image as a generic_image Net7/Document
     */
    public function addMainPhoto(string $filepath, string $type = null)
    {
        // mettere tutto in una funzione
        $f_arr = explode('/', $filepath);
        $filename = Arr::last($f_arr);
        $tempFilepath = '/tmp/'.$filename;
        copy('./storage/seeder/'.$filepath, $tempFilepath);
        $file = new UploadedFile($tempFilepath, $filename, null, null, true);

        $doc = new Document([
            'title' => "Photo for boat {$this->id} of type {$this->boat_type}",
            'file' => $file,
        ]);
        $this->addDocumentWithType($doc, $type ? $type : Document::GENERIC_IMAGE_TYPE);

        return $doc;
    }

    /** DOCS GENERATOR FUNCTIONS **/

    /**
     * Retrieve iamge's path
     *
     * @return string
     */
    public function getMainPhotoPath()
    {
        return $this->getDocumentMediaFilePath(Document::GENERIC_IMAGE_TYPE);
    }

    /**
     * Gets an information array about projects, to be printed on a .docx template
     *
     * @return array
     */
    public function getAllProjectsTableRowInfo()
    {
        $replacements = [];

        foreach ($this->projects as $project) {
            $replacements[] = [
                'row_tableOne' => $project->id,
                'projName' => $project->name,
                'projType' => $project->project_type,
                'projStatus' => $project->project_status,
                'projStart' => $project->start_date, ];
        }

        return $replacements;
    }

    public function updateCurrentTaskPhotosArray()
    {
        if ($this->_currentTask) {
            $index = 1;
            foreach ($this->_currentTask->getDetailedPhotoPaths() as $path) {
                $this->_currentTaskPhotos[$index++] = $path;
            }
            $this->_currentTaskPhotos[$index] = $this->_currentTask->getAdditionalPhotoPath();
        }
    }

    public function getBloccoTaskInfoArray()
    {
        $replacements = [];
        foreach ($this->projects as $project) {
            foreach ($project->tasks as $task) {
                $this->_currentTask = $task;
                $this->updateCurrentTaskPhotosArray();
                $replacements[] =
                    [
                        'task_status' => $task->task_status,
                        'task_type' => $task->intervent_type ? $task->intervent_type->name : '?',
                        'task_location' => $task->section ? $task->section->name : '?',
                        'img_currentTask_img1' => $this->getCurrentTaskImg1(),
                        'img_currentTask_img2' => $this->getCurrentTaskImg2(),
                        'img_currentTask_img3' => $this->getCurrentTaskImg3(),
                        'img_currentTask_img4' => $this->getCurrentTaskImg4(),
                        'img_currentTask_img5' => $this->getCurrentTaskImg5(),
                    ];
            }
        }

        return $replacements;
    }

    public function getBloccoTaskSampleReportInfoArray()
    {
        $replacements = [];
        foreach ($this->projects as $project) {
            foreach ($project->tasks as $task) {
                $this->_currentTask = $task;
                $this->updateCurrentTaskPhotosArray();
                $replacements[] =
                    [
                        'task_id' => $task->id,
                        'task_status' => $task->task_status,
                        'task_description' => $task->description,
                        'task_created_at' => $task->created_at,
                        'task_updated_at' => $task->updated_at,
                        'task_type' => $task->intervent_type ? $task->intervent_type->name : '?',
                        'task_location' => $task->section ? $task->section->name : '?',
                        'pageBreak' => $this->printDocxPageBreak(),
                        'img_currentTask_img1' => $this->getCurrentTaskImg1(),
                        'img_currentTask_img2' => $this->getCurrentTaskImg2(),
                        'img_currentTask_img3' => $this->getCurrentTaskImg3(),
                        'img_currentTask_img4' => $this->getCurrentTaskImg4(),
                        'img_currentTask_img5' => $this->getCurrentTaskImg5(),
                    ];
            }
        }

        return $replacements;
    }

    public function getCurrentTaskImg($index)
    {
        return isset($this->_currentTaskPhotos[$index]) ? $this->_currentTaskPhotos[$index] : '';
    }

    public function getCurrentTaskImg1()
    {
        return $this->getCurrentTaskImg(1);
    }

    public function getCurrentTaskImg2()
    {
        return $this->getCurrentTaskImg(2);
    }

    public function getCurrentTaskImg3()
    {
        return $this->getCurrentTaskImg(3);
    }

    public function getCurrentTaskImg4()
    {
        return $this->getCurrentTaskImg(4);
    }

    public function getCurrentTaskImg5()
    {
        return $this->getCurrentTaskImg(5);
    }

    public function printDocxPageBreak()
    {
        return '</w:t></w:r>'.'<w:r><w:br w:type="page"/></w:r>'.'<w:r><w:t>';
    }

    public function printDocxTodayDate()
    {
        return date('Y-m-d', time());
    }
}
