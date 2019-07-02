<?php

use App\Storm\StormTask;
use Cvsouth\Entities\EntityType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStormTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storm_task', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('entity_id')->unsigned()->nullable();
            $table->timestamps();
        });

        // ereditarietà
        $entity_type = new EntityType(['entity_class' => StormTask::class]);
        $entity_type->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storm_task');

        // ereditarietà
        $entity_type = EntityType::where('entity_class', StormTask::class)->first();
        if ($entity_type) {
            EntityType::destroy([$entity_type->id]);
        };
    }
}
