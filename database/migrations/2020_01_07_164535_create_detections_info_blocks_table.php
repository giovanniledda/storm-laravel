<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetectionsInfoBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detections_info_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->json('detections')->nullable();  // couple "image + key-value" where "key" is a parameter name and "value" an avg value
            $table->text('short_description')->nullable();

            $table->timestamps();

            // Relations:

            // application log section
            $table->unsignedBigInteger('application_log_section_id')->nullable();
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null');

            // tool
            $table->unsignedBigInteger('tool_id')->nullable();
            $table->foreign('tool_id')->references('id')->on('tools')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detections_info_blocks');
    }
}
