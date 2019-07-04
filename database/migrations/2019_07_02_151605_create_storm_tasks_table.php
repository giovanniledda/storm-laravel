<?php

use App\Storm\StormTask;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStormTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storm_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('operation_type', ['idraulic', 'painting', 'electric', 'mechanic', 'damage'])->nullable();
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
        Schema::dropIfExists('storm_tasks');

    }
}
