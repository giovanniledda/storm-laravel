<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('reason')->nullable();
            $table->timestamps();

            // Polymorphic: statusable
            $table->nullableMorphs('statusable');
        });
    }

    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
