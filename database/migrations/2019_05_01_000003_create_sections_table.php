<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->enum('section_type', [SECTION_TYPE_LEFT_SIDE, SECTION_TYPE_RIGHT_SIDE, SECTION_TYPE_DECK])->default(SECTION_TYPE_DECK);
            $table->integer('position')->nullable();
            $table->string('code', 10)->nullable();
            $table->timestamps();

            // Relations:

            // boat
            $table->unsignedBigInteger('boat_id')->nullable();
            $table->foreign('boat_id')->references('id')->on('boats');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sections');
    }
}
