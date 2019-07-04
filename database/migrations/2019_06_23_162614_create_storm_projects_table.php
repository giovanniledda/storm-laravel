<?php

use App\Storm\StormProject;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStormProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storm_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['newbuild', 'refit']);
            $table->string('acronym', 50)->nullable();
            $table->timestamps();

            // Polymorphic: projectable
            $table->nullableMorphs('projectable');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storm_projects');

    }
}
