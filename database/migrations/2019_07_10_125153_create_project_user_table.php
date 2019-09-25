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
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->foreign('profession_id')->references('id')->on('professions')->onDelete('set null');

            $table->unique(['project_id', 'user_id', 'profession_id']);

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
