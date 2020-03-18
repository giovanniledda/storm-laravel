<?php

use App\Section;
use Illuminate\Database\Seeder;

class SectionOverviewImageSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = Section::all();
        $sections->each(function ($section)
        /** @var Section $section */
        {
            $this->command->info("Creating image for Section {$section->id} - {$section->name}");
            $section->drawOverviewImageWithTaskPoints();
            try {
                $this->command->info("...done!");
            } catch (\Exception $e) {
                $this->command->error($e->getMessage());
            }
        });
    }

}
