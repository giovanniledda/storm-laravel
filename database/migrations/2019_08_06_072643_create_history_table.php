<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
           $table->bigIncrements('id'); // 
            $table->text('event_body');
            $table->datetime('event_date'); 
            
            // Polymorphic: commentable
            $table->nullableMorphs('historyable'); 
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
      Schema::dropIfExists('history');
    }
}
