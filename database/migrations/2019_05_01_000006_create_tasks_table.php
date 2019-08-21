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
            $table->string('title')->nullable();
            $table->text('description');
            $table->float('estimated_hours')->nullable();
            $table->float('worked_hours')->nullable();
            $table->boolean('for_admins')->nullable(); 
            $table->string('task_status', 40)->default(TASKS_STATUS_DRAFT);
            $table->boolean('is_open')->default(1);
            $table->boolean('added_by_storm')->default(0);
            $table->float('x_coord')->nullable();
            $table->float('y_coord')->nullable();
            $table->timestamps();

            // Relations:

            // project
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');

            // section
            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');

            // subsection
            $table->unsignedBigInteger('subsection_id')->nullable();
            $table->foreign('subsection_id')->references('id')->on('subsections')->onDelete('set null');

            // user
            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');

            // intervent_type
            $table->unsignedBigInteger('intervent_type_id')->nullable();
            $table->foreign('intervent_type_id')->references('id')->on('task_intervent_types')->onDelete('set null');


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
