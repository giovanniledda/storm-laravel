<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_history', function (Blueprint $table) {
           $table->bigIncrements('id'); // 
           $table->string('event');   
           $table->integer('event_type');
           
           // Relations:
           
           // users
           $table->unsignedBigInteger('author_id')->nullable();
           $table->foreign('author_id')->references('id')->on('users'); 
            // projects
           $table->unsignedBigInteger('project_id');
           $table->foreign('project_id')->references('id')->on('projects'); 
           
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
      Schema::dropIfExists('project_history');
    }
}
