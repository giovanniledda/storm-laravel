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
            $table->enum('operation_type', ['idraulic', 'painting', 'electric', 'mechanic', 'damage'])->nullable();
            $table->timestamps();

            // Relations:

            // project
            $table->unsignedInteger('project_id')->nullable();
//            $table->foreign('project_id')->references('id')->on('projects');

            // section
            $table->unsignedInteger('section_id')->nullable();
//            $table->foreign('section_id')->references('id')->on('sections');

            // user
            $table->unsignedInteger('author_id')->nullable();
//            $table->foreign('author_id')->references('id')->on('users');
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
