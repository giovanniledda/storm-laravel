<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductUseInfoBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_use_info_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->decimal('viscosity', 5, 2)->nullable();
            $table->json('components')->nullable();
            $table->timestamps();

            // Relations:

            // application log section
            $table->unsignedBigInteger('application_log_section_id')->nullable();
            $table->foreign('application_log_section_id')->references('id')->on('application_log_sections')->onDelete('set null');

            // product
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_use_info_blocks');
    }
}
