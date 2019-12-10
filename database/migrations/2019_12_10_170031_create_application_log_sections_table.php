<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationLogSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_log_sections', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->enum('section_type', [
                APPLICATION_LOG_SECTION_TYPE_ZONES,
                APPLICATION_LOG_SECTION_TYPE_PREPARATION,
                APPLICATION_LOG_SECTION_TYPE_APPLICATION,
                APPLICATION_LOG_SECTION_TYPE_INSPECTION
            ])->nullable();
            $table->boolean('is_started')->default(false);
            $table->dateTime('date_hour')->nullable();

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
        Schema::dropIfExists('application_log_sections');
    }
}
