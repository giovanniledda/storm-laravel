<?php

namespace App;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function ucfirst;

class TaskInterventType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function tasks()
    {
        return $this->hasMany(\App\Task::class);
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
        $tit = new self([
                'name' => $faker->bs,  // https://github.com/fzaninotto/Faker#fakerprovideren_uscompany
            ]
        );
        $tit->save();

        return $tit;
    }

    /**
     * @return string
     */
    public function getNameLabelAttribute()
    {
        return ucfirst($this->name);
    }
}
