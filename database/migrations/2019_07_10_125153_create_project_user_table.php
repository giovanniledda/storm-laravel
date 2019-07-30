<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->bigIncrements('id');
           // $table->enum('role', [PROJECT_USER_ROLE_AUTHOR, PROJECT_USER_ROLE_OWNER]);  // TODO: forse un enum è limitante...possiamo pensare di agigungere un ruolo (usando il Model "Role") alla relazione
           
            // Relations:
            $table->unsignedBigInteger('profession_id');
            $table->foreign('profession_id')->references('id')->on('professions');
              
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects');

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
        Schema::dropIfExists('project_user');
    }
}
