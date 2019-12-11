<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('producer');
            $table->string('p_type')->nullable();
            $table->decimal('sv_percentage', 8, 3)->nullable();
            $table->json('components')->nullable();

            $table->timestamps();

            // Relations:

            // product_use_info_blocks
            $table->unsignedBigInteger('product_use_info_block_id')->nullable();
            $table->foreign('product_use_info_block_id')->references('id')->on('product_use_info_blocks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
