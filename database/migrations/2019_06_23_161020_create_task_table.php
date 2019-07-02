<?php

use App\Task;
use Cvsouth\Entities\EntityType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->integer('entity_id')->unsigned()->nullable();
            $table->timestamps();

            // relations
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('project');
        });

        // ereditarietà
        $entity_type = new EntityType(['entity_class' => Task::class]);
        $entity_type->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');

        // ereditarietà
        $entity_type = EntityType::where('entity_class', Task::class)->first();
        if ($entity_type) {
            EntityType::destroy([$entity_type->id]);
        };
    }
}
