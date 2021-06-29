<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThinnersToProdUseInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_use_info_blocks', function (Blueprint $table) {
            $table->json('thinners')->nullable()->after('components');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_use_info_blocks', function (Blueprint $table) {
            $table->dropColumn('thinners');
        });
    }
}
