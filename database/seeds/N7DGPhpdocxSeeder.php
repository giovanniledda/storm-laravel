<?php

use App\MyTemplateProcessor;
use App\Project;
use App\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Net7\DocsGenerator\DocsGenerator;
use Seeds\SeederUtils;

// TODO: copiami nel package alla fine!!!!!!!!!!!!!!!!!

class N7DGPhpdocxSeeder extends Seeder
{
    protected $utils;
    protected $faker;
    protected $dg;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $this->utils = new SeederUtils();
        $project = Project::find(1);

        $this->command->warn(" ------ MANAGE TEMPLATE (corrosion_map) --------");

        $category = $project->persistAndAssignTemplateCategory('corrosion_map');
        $placeholders = [
            '$break_n1$' => null,  // riconosciuto dal sistema
            '$html_bloccoTask$' => 'getBlockHtml()',
            '$boat_type$' => 'getBoatType()',
            '$boat_name$' => 'getBoatName()'
        ];
        $project->insertPlaceholders('corrosion_map', $placeholders, true);


        $this->command->warn(" ------ MANAGE TEMPLATE (environmental_report) --------");

        $category = $project->persistAndAssignTemplateCategory('environmental_report');
        $placeholders = [
            '$date$' => 'currentDate()',
            '$temp_start_date$' => 'getEnvironmentalParamFirstMeasureDate()-celsius__app\_project',
            '$temp_end_date$' => 'getEnvironmentalParamLastMeasureDate()-celsius__app\_project',
            '$temp_max$' => 'getEnvironmentalParamMax()-celsius__app\_project',
            '$temp_min$' => 'getEnvironmentalParamMin()-celsius__app\_project',
            '$temp_avg$' => 'getEnvironmentalParamAvg()-celsius__app\_project',
            '$temp_std$' => 'getEnvironmentalParamStd()-celsius__app\_project',
            '$chart_temperatureChart$' => null, // entra in gioco la funzione handlePhpdocxCharts che andrà implementata nel model User

            '$dp_start_date$' => 'getEnvironmentalParamFirstMeasureDate()-dew_point__app\_project',
            '$dp_end_date$' => 'getEnvironmentalParamLastMeasureDate()-dew_point__app\_project',
            '$dp_max$' => 'getEnvironmentalParamMax()-dew_point__app\_project',
            '$dp_min$' => 'getEnvironmentalParamMin()-dew_point__app\_project',
            '$dp_avg$' => 'getEnvironmentalParamAvg()-dew_point__app\_project',
            '$dp_std$' => 'getEnvironmentalParamStd()-dew_point__app\_project',
            '$chart_dewpointChart$' => null, // entra in gioco la funzione handlePhpdocxCharts che andrà implementata nel model User

            '$hum_start_date$' => 'getEnvironmentalParamFirstMeasureDate()-humidity__app\_project',
            '$hum_end_date$' => 'getEnvironmentalParamLastMeasureDate()-humidity__app\_project',
            '$hum_max$' => 'getEnvironmentalParamMax()-humidity__app\_project',
            '$hum_min$' => 'getEnvironmentalParamMin()-humidity__app\_project',
            '$hum_avg$' => 'getEnvironmentalParamAvg()-humidity__app\_project',
            '$hum_std$' => 'getEnvironmentalParamStd()-humidity__app\_project',
            '$chart_humidityChart$' => null, // entra in gioco la funzione handlePhpdocxCharts che andrà implementata nel model User

        ];
        $project->insertPlaceholders('environmental_report', $placeholders, true);
    }
}
