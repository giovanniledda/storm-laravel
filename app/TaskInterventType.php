<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Faker\Generator as Faker;

class TaskInterventType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function tasks()
    {
        return $this->hasMany('App\Task');
    }



    /**
     * Creates a Task Intervent Type using some fake data and some others that have sense
     *
     * @param Faker $faker
     *
     * @return TaskInterventType $tit
     */
    public static function createSemiFake(Faker $faker)
    {
        $tit = new TaskInterventType([
                'name' => $faker->bs,  // https://github.com/fzaninotto/Faker#fakerprovideren_uscompany
            ]
        );
        $tit->save();
        return $tit;
    }
}
