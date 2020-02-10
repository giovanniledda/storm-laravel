<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditorToApplicationLog extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_logs', function (Blueprint $table) {

            // Relations:

            // user
            $table->unsignedBigInteger('last_editor_id')->nullable();
            $table->foreign('last_editor_id')->references('id')->on('users')->onDelete('set null');
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
            $table->dropForeign(['last_editor_id']);
        });
    }
}
