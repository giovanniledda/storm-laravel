<?php

namespace App\Traits;

use App\User;
use Faker\Factory as FakerFactory;
use Net7\DocsGenerator\Utils;
use Net7\EnvironmentalMeasurement\Models\EnvironmentalParameter;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use Phpdocx\Create\CreateDocxFromTemplate;
use Phpdocx\Elements\WordFragment;
use function count;
use function date;
use function time;

trait TemplateReplacementRules
{


    public function handleBloccoProvaHTML(CreateDocxFromTemplate &$template_processor)
    {
        $faker = FakerFactory::create();

        $users = User::take(5)->get();
        for ($i = 0; $i < count($users) - 1; $i++) {
            $template_processor->cloneBlock('bloccoTask');
        }

        foreach ($users as $user) {
            $template_processor->replaceVariableByText([
                'task_id' => $user->id,
                'task_location' => $user->getFakeLocation(),
                'task_type' => $user->getFullname(),
                'task_description' => $user->getLoremIpsum(),
                'task_created_at' => $user->created_at,
                'task_updated_at' => $user->updated_at,
            ], ['firstMatch' => true]);

            $template_processor->replaceVariableByHTML('pageBreak', 'block', '<p style="page-break-before: always;"></p>');

            $image_opts = Utils::extractImageOptionsFromPlaceholder('$img_logo1_clone:6:6$');
            $template_processor->replacePlaceholderImage('img_logo1_clone:6:6', $faker->image('/tmp', '200', '200', 'cats'), $image_opts);

            $image_opts = Utils::extractImageOptionsFromPlaceholder('$img_logo2_clone:7:7$');
            $template_processor->replacePlaceholderImage('img_logo2_clone:7:7', $faker->image('/tmp', '200', '200', 'cats'), $image_opts);
        }
    }

    /**
     * @param CreateDocxFromTemplate $template_processor
     * @param string $block_name
     */
    public function handlePhpdocxBlockCloning(CreateDocxFromTemplate &$template_processor, string $block_name)
    {
        switch ($block_name) {
            case 'bloccoTask':
                $this->handleBloccoProvaHTML($template_processor);
        }
    }

    /**
     * Insert a chart with measurement values for a specific param
     *
     * @param CreateDocxFromTemplate $template_processor
     * @param string $chart_name
     * @param string $param_key
     * @param string $legend
     * @param string $color
     */
    public function handleEnvironmentalParamChart(CreateDocxFromTemplate &$template_processor, string $chart_name, string $param_key, string $legend, string $color = '4')
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {

            $data = [
                'legend' => [$legend],
            ];

            /** @var Measurement $measurement */
            $i = 0;
            $hax_print_step = 10;
            foreach ($env_param->measurements as $measurement) {
                $step = ++$i%$hax_print_step;
                $r=0;
                $data['data'][] =
                    [
                        'name' => ($step == 0) ? $measurement->measurement_time : '',
                        'values' => [$measurement->measured_value]
                    ];
            }

            $uom = $env_param->unity_of_measure;
            $vax_label = "$legend ($uom)";
            $paramsChart = array(
                'data' => $data,
                'type' => 'lineChart',
                'color' => $color,
                'chartAlign' => 'center',
                'showTable' => 0,
                'sizeX' => '18',
                'sizeY' => '15',
                'legendPos' => 'b',
                'legendOverlay' => '0',
                'haxLabel' => 'Time',
                'vaxLabel' => 0,
                'haxLabelDisplay' => 'vertical',
                'vaxLabelDisplay' => 0,
                'hgrid' => '1',
                'vgrid' => '1',
                'scalingMax' => $env_param->getMaximum(),
                'scalingMin' => $env_param->getMinimum(),
                'horizontalOffset' => 360,
                'formatDataLabels' => [
                    'rotation' => 45,
                    'position' => 'center'
                ],
            );

            $chart = new WordFragment($template_processor, 'document');
            $chart->addChart($paramsChart);

            $template_processor->replaceVariableByWordFragment(array($chart_name => $chart), array('type' => 'block'));
        }
    }


    /**
     * @param CreateDocxFromTemplate $template_processor
     * @param string $chart_name
     */
    public function handlePhpdocxCharts(CreateDocxFromTemplate &$template_processor, string $chart_name)
    {
        $param_key = $legend = $color = '';
        switch ($chart_name) {
            case 'chart_temperatureChart':
                $param_key = 'celsius__app\_user';
                $legend = 'Temperature';
                $color = '4';
                break;
            case 'chart_dewpointChart':
                $param_key = 'dew_point__app\_user';
                $legend = 'Dew Point';
                $color = '5';
                break;
            case 'chart_humidityChart':
                $param_key = 'humidity__app\_user';
                $legend = 'Humidity';
                $color = '6';
                break;
        }
        $this->handleEnvironmentalParamChart($template_processor, $chart_name, $param_key, $legend, $color);
    }


    public function getEnvironmentalParamFirstMeasureDate($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            return $env_param->getMinTime();
        }
    }

    public function getEnvironmentalParamLastMeasureDate($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            return $env_param->getMaxTime();
        }
    }

    public function getEnvironmentalParamMax($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            return $env_param->getMaximum();
        }
    }

    public function getEnvironmentalParamMin($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            return $env_param->getMinimum();
        }
    }

    public function getEnvironmentalParamAvg($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            return $env_param->getAverage();
        }
    }

    public function getEnvironmentalParamStd($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            return $env_param->getStandardDeviation();
        }
    }

    /**
     * Called by DocsGenerator
     * @return false|string
     */
    public function currentDate()
    {
        return date('Y-m-d', time());
    }


}
