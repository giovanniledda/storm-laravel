<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->enum('role', ['commander', 'owner']);  // TODO: forse un enum è limitante...possiamo pensare di agigungere un ruolo (usando il Model "Role") alla relazione
            $table->timestamps();

            // Relations:

            $table->unsignedInteger('boat_id');
            $table->unsignedInteger('user_id');
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
