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
           // $table->enum('role', ['commander', 'owner']);  // TODO: forse un enum è limitante...possiamo pensare di agigungere un ruolo (usando il Model "Role") alla relazione
            
             // Relations:
            
            $table->unsignedBigInteger('profession_id');
            $table->foreign('profession_id')->references('id')->on('professions'); 

            $table->unsignedBigInteger('boat_id');
            $table->foreign('boat_id')->references('id')->on('boats');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
            
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
