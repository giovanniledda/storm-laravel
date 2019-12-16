<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code');
            $table->string('description')->nullable();
            $table->decimal('extension', 6, 2)->nullable();

            $table->timestamps();

            // Relations:

            // If this is null, the zone is a "root" (or parent) zone
            $table->unsignedBigInteger('parent_zone_id')->nullable();
            $table->foreign('parent_zone_id')->references('id')->on('zones')->onDelete('cascade');

            // project
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('zones');
    }
}
