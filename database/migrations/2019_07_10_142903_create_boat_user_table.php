<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoatUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boat_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->enum('role', ['commander', 'owner']);  // TODO: forse un enum è limitante...possiamo pensare di agigungere un ruolo (usando il Model "Role") alla relazione

            // Relations:

            $table->unsignedBigInteger('profession_id')->nullable();
            $table->foreign('profession_id')->references('id')->on('professions')->onDelete('set null');

            $table->unsignedBigInteger('boat_id');
            $table->foreign('boat_id')->references('id')->on('boats')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boat_user');
    }
}
