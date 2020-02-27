<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationLogsTasksTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_logs_tasks', function (Blueprint $table) {

            $table->enum('action', ['open', 'close'])->default('open');

            // Relations:

            // task (actually, only remark)
            $table->unsignedBigInteger('task_id')->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');

            // application log
            $table->unsignedBigInteger('application_log_id')->nullable();
            $table->foreign('application_log_id')->references('id')->on('application_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications_logs_tasks');
    }
}
