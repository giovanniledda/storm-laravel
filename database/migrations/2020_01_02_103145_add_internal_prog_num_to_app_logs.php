<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInternalProgNumToAppLogs extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_logs', function (Blueprint $table) {
            $table->integer('internal_progressive_number')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_logs', function (Blueprint $table) {
            $table->dropColumn('internal_progressive_number');
        });
    }
}
