<?php

use App\Task;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('number')->nullable();
            $table->string('title');
            $table->text('description');
            $table->float('estimated_hours')->nullable();
            $table->float('worked_hours')->nullable();
            $table->boolean('for_admins')->nullable();
            $table->timestamps();

            // Relations:

            // project
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects');

            // section
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->foreign('subsection_id')->references('id')->on('subsections');

            // user
            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users');

            // intervent_type
            $table->unsignedBigInteger('intervent_type_id')->nullable();
            $table->foreign('intervent_type_id')->references('id')->on('task_intervent_types');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');

    }
}
