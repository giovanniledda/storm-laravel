<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Task;
use function factory;

class ModelTaskTest extends TestCase
{
    /**
     *
     * @return void
     * @throws \Spatie\ModelStatus\Exceptions\InvalidStatus
     */
    public function testSimpleCreation()
    {

        $data = Task::getSemiFakeData($this->faker);
        $except = [
            'is_open',
            'project_id',
            'section_id',
            'subsection_id',
            'author_id',
            'intervent_type_id',
        ];

        $task1 = factory(Task::class)->create();
        $this->checkAllFields($task1, $data, $except);

        $task2 = Task::create($data);
        $this->checkAllFields($task2, $data, $except);

        $task3 = Task::createSemiFake($this->faker);
        $this->checkAllFields($task3, $data, $except);

        $this->assertCount(3, Task::all());
    }

    public function testPublicPrivate()
    {
        $pub_tasks_created = factory(Task::class, 5)->create(
          ['is_private' => 0]
        );
        $priv_tasks_created = factory(Task::class, 15)->create(
            ['is_private' => 1]
        );

        $this->assertCount(20, Task::all());

        $this->assertCount(5, Task::public()->get());

        $this->assertCount(15, Task::private()->get());
    }

    public function testSoftDelete()
    {
        $pub_tasks_created = factory(Task::class, 5)->create(
          ['is_private' => 0]
        );
        $pub_task1 = factory(Task::class)->create(
            ['is_private' => 0]
        );
        $pub_task2 = factory(Task::class)->create(
            ['is_private' => 0]
        );
        $pub_task3 = factory(Task::class)->create(
            ['is_private' => 0]
        );


        $priv_tasks_created = factory(Task::class, 15)->create(
            ['is_private' => 1]
        );

        $this->assertCount(23, Task::all());

        $pub_task1->delete();
        $this->assertTrue($pub_task1->trashed());
        $pub_task2->delete();
        $this->assertTrue($pub_task2->trashed());

        Task::public()->delete();
        $this->assertFalse($pub_task3->trashed());  // anche se ho eliminato sopra tutti i trashed, questo ?? false. Funziona cos??, no comment.

        $this->assertCount(15, Task::all());

        $this->assertCount(23, Task::withTrashed()->get());

        $this->assertCount(8, Task::onlyTrashed()->get());

        Task::withTrashed()->restore();
        $this->assertCount(23, Task::all());

        Task::public()->forceDelete();
        $this->assertCount(15, Task::all());
        $this->assertCount(15, Task::withTrashed()->get());

    }
}
