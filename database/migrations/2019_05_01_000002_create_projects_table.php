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
            $table->string('status', 40)->default('open');
            $table->timestamps();

            // Relations:

            // user (N to M)
            // project_user

            // site
            $table->unsignedBigInteger('site_id')->nullable();
            $table->foreign('site_id')->references('id')->on('sites');

            // boat
            $table->unsignedBigInteger('boat_id')->nullable();
            $table->foreign('boat_id')->references('id')->on('boats');

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
