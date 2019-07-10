<?php

use App\Project;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('type', ['newbuild', 'refit']);
            $table->string('acronym', 50)->nullable();
            $table->timestamps();

            // Relations:

            // user (N to M)
            // project_user

            // boat
            $table->unsignedInteger('boat_id')->nullable();
//            $table->foreign('boat_id')->references('id')->on('boats');

            // site
            $table->unsignedInteger('site_id')->nullable();
//            $table->foreign('site_id')->references('id')->on('sites');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
