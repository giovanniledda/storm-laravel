<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeToBlocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_use_info_blocks', function (Blueprint $table) {
            $table->dropForeign('product_use_info_blocks_application_log_section_id_foreign');
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('cascade');
        });
        Schema::table('detections_info_blocks', function (Blueprint $table) {
            $table->dropForeign('detections_info_blocks_application_log_section_id_foreign');
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('cascade');
        });
        Schema::table('generic_data_info_blocks', function (Blueprint $table) {
            $table->dropForeign('generic_data_info_blocks_application_log_section_id_foreign');
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('cascade');
        });
        Schema::table('zone_analysis_info_blocks', function (Blueprint $table) {
            $table->dropForeign('zone_analysis_info_blocks_application_log_section_id_foreign');
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
