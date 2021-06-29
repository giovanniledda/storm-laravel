<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportableToReportItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_items', function (Blueprint $table) {
            // Polymorphic: reportable
            $table->nullableMorphs('reportable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_items', function (Blueprint $table) {
            $table->dropColumn(['reportable_id', 'reportable_type']);
        });
    }
}
