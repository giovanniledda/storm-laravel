<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->string('registration_number', 100);
            $table->string('flag', 50)->nullable();
            $table->year('manufacture_year')->nullable();
            $table->double('length', 8, 2)->nullable();
            $table->double('draft', 8, 2)->nullable();
            $table->double('beam', 8, 2)->nullable();
            $table->timestamps();

            // Relations:

            // user (N to M)
            // boat_user

            // site
//            $table->unsignedInteger('site_id')->nullable();
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
        Schema::dropIfExists('boats');
    }
}
