<?php

namespace App\Traits;

use App\Task;
use App\User;
use Faker\Factory as FakerFactory;
use Net7\DocsGenerator\Utils;
use Net7\EnvironmentalMeasurement\Models\EnvironmentalParameter;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use Phpdocx\Create\CreateDocxFromTemplate;
use Phpdocx\Elements\WordFragment;
use function count;
use function date;
use function fclose;
use function min;
use function time;
use function unlink;

trait TemplateReplacementRules
{

    // Usate con il DocsGenerator: per corrosion_map
    protected $_currentTask;
    protected $_currentTaskPhotos;
    protected $_taskToIncludeInReport;
    protected $_openFiles = [];

    // Usate con il DocsGenerator: per environmental_report
    protected $_current_date_start;
    protected $_current_date_end;
    protected $_current_min_tresholds;

    /**
     * *****************************
     * *****************************  TEMPLATE: corrosion_map
     * *****************************
     *
     */

    public function getBoatName(){
        $boat = $this->boat;
        return Utils::sanitizeTextsForPlaceholders($boat->name);
    }

    public function getBoatRegistrationNumber(){
        $boat = $this->boat;
        return Utils::sanitizeTextsForPlaceholders($boat->registration_number);
    }

    public function getBoatType(){
        $boat = $this->boat;
        return $boat->boat_type;
    }

    public function getBoatMainPhotoPath(){

        $boat = $this->boat;
        return $boat->getMainPhotoPath();
    }


    public function printDocxPageBreak()
    {
        return '</w:t></w:r>'.'<w:r><w:br w:type="page"/></w:r>'. '<w:r><w:t>';
    }

    public function printDocxTodayDate()
    {
        return date('Y-m-d', time());
    }

    public function getBloccoTaskSampleReportInfoArray()
    {
        $replacements = [];
        foreach ($this->getTasksToIncludeInReport() as $task) {
            $this->_currentTask = $task;
            $this->updateCurrentTaskPhotosArray();
            $repl_array =
                [
                    'task_id' => $task->id,
                    'task_status' => Utils::sanitizeTextsForPlaceholders($task->task_status),
                    'task_description' => Utils::sanitizeTextsForPlaceholders($task->description),
                    'task_created_at' => $task->created_at,
                    'task_updated_at' => $task->updated_at,
                    'task_type' => $task->intervent_type ? Utils::sanitizeTextsForPlaceholders($task->intervent_type->name) : '?',
                    'task_location' => $task->section ? Utils::sanitizeTextsForPlaceholders($task->section->name) : '?',
                    'pageBreak' => $this->printDocxPageBreak(),
                    'img_currentTask_brPos' => $this->getCurrentTaskBridgeImage(),
                    'img_currentTask_img1' => $this->getCurrentTaskImg1(),
                    'img_currentTask_img2' => $this->getCurrentTaskImg2(),
                    'img_currentTask_img3' => $this->getCurrentTaskImg3(),
                    'img_currentTask_img4' => $this->getCurrentTaskImg4(),
                    'img_currentTask_img5' => $this->getCurrentTaskImg5(),
                ];
            // for ($i = 1; $i <= 5; $i++) {
            //     if ($this->getCurrentTaskImg($i)) {
            //         $repl_array["img_currentTask_img$i"] = $this->getCurrentTaskImg($i);
            //     }
            // }
            $replacements[] = $repl_array;
        }
        return $replacements;
    }

    public function getCurrentTaskImg($index, $task_id = null)
    {
        if ($task_id) {
            $this->_currentTask = Task::find($task_id);
            $this->updateCurrentTaskPhotosArray();
        }
        return isset($this->_currentTaskPhotos[$index]) ? $this->_currentTaskPhotos[$index] : '';
    }

    public function getCurrentTaskImg1($task_id = null)
    {
        return $this->getCurrentTaskImg(1, $task_id);
    }

    public function getCurrentTaskImg2($task_id = null)
    {
        return $this->getCurrentTaskImg(2, $task_id);
    }

    public function getCurrentTaskImg3($task_id = null)
    {
        return $this->getCurrentTaskImg(3, $task_id);
    }

    public function getCurrentTaskImg4($task_id = null)
    {
        return $this->getCurrentTaskImg(4, $task_id);
    }

    public function getCurrentTaskImg5($task_id = null)
    {
        return $this->getCurrentTaskImg(5, $task_id);
    }

    public function updateCurrentTaskPhotosArray()
    {
        $this->_currentTaskPhotos = [];
        if ($this->_currentTask) {
            $index = 1;
            foreach ($this->_currentTask->getDetailedPhotoPaths() as $path) {
                $this->_currentTaskPhotos[$index++] = $path;
            }
            $this->_currentTaskPhotos[5] = $this->_currentTask->getAdditionalPhotoPath();
        }
    }

    public function getCurrentTaskBridgeImage($task_id = null)
    {
        if ($task_id) {
            $this->_currentTask = Task::find($task_id);
        }

        if ($this->_currentTask && $this->_currentTask->bridge_position) {
            $data = $this->_currentTask->generateBridgePositionFileFromBase64();

            $this->_openFiles[] = $data;
            return $data['path'];
        }

        return '';
    }

    public function setTasksToIncludeInReport($tasks)
    {
        $this->_taskToIncludeInReport = $tasks ? $tasks : [];
    }

    public function getTasksToIncludeInReport()
    {
        if (!empty($this->_taskToIncludeInReport)) {
            $tasks = [];
            foreach ($this->_taskToIncludeInReport as $task_id) {
                $tasks[] = Task::Find($task_id);
            }
            return $tasks;
        } else {
            return $this->tasks;  // è la chiamata alla relazione Eloquent. Si presuppone che il model abbia dei Task
        }
    }

    public function closeAllTasksTemporaryFiles(){
        foreach ($this->_openFiles as $data){
            fclose($data['handle']);
            unlink($data['path']);
        }
    }
    public function getPageBreak() {
        return '<p style="page-break-before: always;"></p>';
    }


    public function getCorrosionMapHtmlBlock()
    {
        /** @var Task $task */
        $html = '';
        $tasks = $this->getTasksToIncludeInReport();
        foreach ($tasks as $task) {
            $this->_currentTask = $task;
            $this->updateCurrentTaskPhotosArray();
            $html .= $task->getCorrosionMapHtml($this->_currentTaskPhotos);
        }
        return $html;
    }


    public function setupTemplate()
    {
        $category = $this->persistAndAssignTemplateCategory('corrosion_map');
        $placeholders = [

            '$pageBreak$' => 'getPageBreak()',
            '$html_bloccoTask$' => 'getBlockHtml()',
            '$boat_type$' => 'getBoatType()',
            '$boat_name$' => 'getBoatName()'

            // '${boat_name}' => 'getBoatName()',
            // '${boat_reg_num}' => 'getBoatRegistrationNumber()',
            // '${boat_type}' => 'getBoatType()',
            // '${img_BoatImage:250:250:false}' => 'getBoatMainPhotoPath()',
            // '${date}' => 'printDocxTodayDate()',
            // '${blC_bloccoTask}' => 'getBloccoTaskSampleReportInfoArray()',
            // '${pageBreak}' => 'printDocxPageBreak()',
//            '${row_tableOne}' => 'getTableTaskSampleReportInfoArray()',
//            '${img_currentTask_brPos:450:450:false}' => 'getCurrentTaskBridgeImage()',
//            '${img_currentTask_img1}' => 'getCurrentTaskImg1()',
//            '${img_currentTask_img2}' => 'getCurrentTaskImg2()',
//            '${img_currentTask_img3}' => 'getCurrentTaskImg3()',
//            '${img_currentTask_img4}' => 'getCurrentTaskImg4()',
//            '${img_currentTask_img5}' => 'getCurrentTaskImg5()',
        ];

        $this->insertPlaceholders('corrosion_map', $placeholders, true);
    }


    /**
     * *****************************
     * *****************************  TEMPLATE: environmental_report
     * *****************************
     *
     */


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
                'legend' => $env_param->min_threshold ? ['Min Threshold', $legend] : [$legend],
            ];

            /** @var Measurement $measurement */
            $i = 0;
            $hax_print_step = 10;
            foreach ($env_param->measurements as $measurement) {
                $step = ++$i%$hax_print_step;
                $data['data'][] =
                    [
                        'name' => ($step == 0) ? $measurement->measurement_time : '',
                        'values' => $env_param->min_threshold ? [$env_param->min_threshold, $measurement->measured_value] : [$measurement->measured_value]
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
                'scalingMin' => $env_param->min_threshold ? min($env_param->min_threshold, $env_param->getMinimum()) : $env_param->getMinimum(),
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
                $param_key = 'celsius__app\_project';
                $legend = 'Temperature';
                $color = '2';
                break;
            case 'chart_dewpointChart':
                $param_key = 'dew_point__app\_project';
                $legend = 'Dew Point';
                $color = '2';
                break;
            case 'chart_humidityChart':
                $param_key = 'humidity__app\_project';
                $legend = 'Humidity';
                $color = '2';
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
