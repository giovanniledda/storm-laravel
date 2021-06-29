<?php

use App\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->text('name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('project_progress')->default(0);
            $table->enum('project_type', [PROJECT_TYPE_NEWBUILD, PROJECT_TYPE_REFIT])->default(PROJECT_TYPE_REFIT);
            $table->string('acronym', 50)->nullable();
            $table->integer('imported')->default(0);
            $table->string('project_status', 40)->default(PROJECT_STATUS_OPERATIONAL);
            $table->timestamp('last_cloud_sync')->nullable();

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
