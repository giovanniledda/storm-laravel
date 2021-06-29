<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->enum('application_type', [
                APPLICATION_TYPE_PRIMER,
                APPLICATION_TYPE_FILLER,
                APPLICATION_TYPE_HIGHBUILD,
                APPLICATION_TYPE_UNDERCOAT,
                APPLICATION_TYPE_COATING,
            ])->nullable();

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
        Schema::dropIfExists('application_logs');
    }
}
