<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGenericDataInfoBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generic_data_info_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->json('key_value_infos')->nullable();

            $table->timestamps();

            // Relations:

            // application log section
            $table->unsignedBigInteger('application_log_section_id')->nullable();
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generic_data_info_blocks');
    }
}
