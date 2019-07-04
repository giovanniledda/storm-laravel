<?php

use App\Storm\StormProject;
use Cvsouth\Entities\EntityType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStormProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storm_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->integer('entity_id')->unsigned()->nullable();
            $table->timestamps();

            // Polymorphic: projectable
            $table->nullableMorphs('projectable');
        });

        // ereditarietà
        $entity_type = new EntityType(['entity_class' => StormProject::class]);
        $entity_type->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storm_projects');

        // ereditarietà
        $entity_type = EntityType::where('entity_class', StormProject::class)->first();
        if ($entity_type) {
            EntityType::destroy([$entity_type->id]);
        };
    }
}
