<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('report_type');
            $table->string('report_name');
            $table->json('data_attributes')->nullable();  // serie di info che variano a seconda del tipo di report
            $table->json('report_links')->nullable();  // link variabili a seconda del tipo di report
            $table->dateTime('report_create_date')->nullable(); // non uso i timestamp per questo dato perché potrebbero volere lo storico
            $table->dateTime('report_update_date')->nullable(); // non uso i timestamp per questo dato perché potrebbero volere lo storico
            // Relations:

            // non è una vera e propria chiave esterna, può puntare ad un oggetto qualsiasi tra log, doc, app_log, etc.
            $table->unsignedBigInteger('report_id')->nullable();

            // user: può essere il creatore o l'autore dell'ultima modifica
            $table->unsignedBigInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');

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
        Schema::dropIfExists('report_items');
    }
}
