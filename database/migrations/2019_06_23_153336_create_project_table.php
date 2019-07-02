<?php

use App\Project;
use Cvsouth\Entities\EntityType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('entity_id')->unsigned()->nullable();
            $table->timestamps();

            // relations
            $table->nullableMorphs('projectable');
        });

        // ereditarietà
        $entity_type = new EntityType(['entity_class' => Project::class]);
        $entity_type->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project');

        // ereditarietà
        $entity_type = EntityType::where('entity_class', Project::class)->first();
        if ($entity_type) {
            EntityType::destroy([$entity_type->id]);
        };
    }
}
