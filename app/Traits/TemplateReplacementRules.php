<?php



namespace App\Traits;

use App\Section;
use App\Task;
use App\User;
use Faker\Factory as FakerFactory;
use Net7\DocsGenerator\Utils;
use Net7\EnvironmentalMeasurement\Models\EnvironmentalParameter;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use Phpdocx\Create\CreateDocxFromTemplate;
use Phpdocx\Elements\WordFragment;

use function ceil;
use function count;
use function date;
use function fclose;
use function getimagesize;
use function min;
use function throw_if;
use function time;
use function unlink;

defined('MEASUREMENT_DEFAULT_DATA_SOURCE') or define('MEASUREMENT_DEFAULT_DATA_SOURCE', 'STORM - Web App Frontend');

trait TemplateReplacementRules
{

    // Usate con il DocsGenerator: per corrosion_map
    protected $_currentTask;
    protected $_currentTaskPhotos;
    protected $_taskToIncludeInReport;
    protected $_only_public_tasks;
    protected $_openFiles = [];

    // Usate con il DocsGenerator: per environmental_report
    protected $_current_date_start;
    protected $_current_date_end;
//    protected $_current_date_start = '2017-12-29';
//    protected $_current_date_end = '2018-01-08';
    protected $_current_data_source;
    protected $_current_min_tresholds;

    /**
     * *****************************
     * *****************************  TEMPLATE: corrosion_map
     * *****************************
     *
     */

    public function getBoatName()
    {
        $boat = $this->boat;
        return Utils::sanitizeTextsForPlaceholders($boat->name);
    }

    public function getBoatRegistrationNumber()
    {
        $boat = $this->boat;
        return Utils::sanitizeTextsForPlaceholders($boat->registration_number);
    }

    public function getBoatType()
    {
        $boat = $this->boat;
        return $boat->boat_type;
    }

    public function getBoatMainPhotoPath()
    {

        $boat = $this->boat;
        return $boat->getMainPhotoPath();
    }


    public function printDocxPageBreak()
    {
        return '</w:t></w:r>' . '<w:r><w:br w:type="page"/></w:r>' . '<w:r><w:t>';
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
        if ($this->_currentTask && $this->_currentTask->getFirstHistory()) {
            $index = 1;
            foreach ($this->_currentTask->getFirstHistory()->getDetailedPhotoPaths() as $path) {
                $this->_currentTaskPhotos[$index++] = $path;
            }
            $this->_currentTaskPhotos[5] = $this->_currentTask->getFirstHistory()->getAdditionalPhotoPath();
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

    public function setTasksToIncludeInReport($tasks, $only_public = null)
    {
        $this->_taskToIncludeInReport = $tasks ? $tasks : [];
        $this->_only_public_tasks = $only_public;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function getTasksToIncludeInReport()
    {
        if (!empty($this->_taskToIncludeInReport)) {
            $tasks = [];
            foreach ($this->_taskToIncludeInReport as $task_id) {
                if ($task_obj = $this->_only_public_tasks ? Task::findPublic($task_id) : Task::find($task_id)) {
                    $tasks[] = $task_obj;
                }
            }

            throw_if(empty($tasks), 'Exception', 'No data available in this range.');
            return $tasks;
        } else {
            return $this->tasks;  // è la chiamata alla relazione Eloquent. Si presuppone che il model abbia dei Task
        }
    }

    public function closeAllTasksTemporaryFiles()
    {
        foreach ($this->_openFiles as $data) {
            fclose($data['handle']);
            unlink($data['path']);
        }
    }

    /**
     * Stampa nel docx l'htlm relativo ai task
     *
     * @return string
     * @throws \Throwable
     */
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


    /**
     * Stampa nel docx l'htlm relativo all'indice
     *
     * @return string
     * @throws \Throwable
     */
    public function getCorrosionMapHtmlTableOfContents()
    {
        $html = '<p style="text-align: center;font-size: 21px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Table of Contents</p>';
        $html .= '<table cellpadding="0" cellspacing="0"><tbody>';
        $tasks = $this->getTasksToIncludeInReport();
        $toc_pages = ceil(count($tasks)/46);
        $task_ids = $this->_taskToIncludeInReport ?? $this->tasks()->pluck('id')->toArray();
        $sections = Section::getSectionsStartingFromTasks($task_ids);
        $section_overview_pages = ceil(count($sections)/4);
        $index = 1 + $toc_pages + $section_overview_pages;
        /** @var Task $task */
        foreach ($tasks as $task) {
            $this->_currentTask = $task;
            $this->updateCurrentTaskPhotosArray();
            $index = count($this->_currentTaskPhotos) > 4 ? ($index + 2) : ($index + 1);
            $point_id = $task->internal_progressive_number;
            $task_location = $task->section ? Utils::sanitizeTextsForPlaceholders($task->section->name) : '?';
            $html .= <<<EOF
                    <tr>
                        <td width="496" style="border-bottom: 1px solid #ececec; margin-bottom: 8px"><b>Task #$point_id</b> ($task_location)</td>
                        <td width="200" style="border-bottom: 1px solid #ececec; text-align: right;">Pag. $index</td>
                    </tr>
EOF;
        }
        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Stampa nel docx l'htlm relativo alle immagini delle sezioni con tutti i pin sopra
     *
     * @return string
     * @throws \Throwable
     */
    public function getCorrosionMapHtmlSectionImgsOverview()
    {
        /** @var Task $task */
        $html = '<table cellpadding="0" cellspacing="0"><tbody>';
        $task_ids = $this->_taskToIncludeInReport ?? $this->tasks()->pluck('id')->toArray();
        $sections = Section::getSectionsStartingFromTasks($task_ids);

        // 1 - prendo l'img di section con la W maggiore
        $max_w = 0;
        /** @var Section $section */
        foreach ($sections as $section) {
            $deck_media = $section->generic_images->last();
            $deck_img_path = $deck_media->getPathBySize('');
            $bridgeImageInfo = getimagesize($deck_img_path);
            $max_w = max($max_w, $bridgeImageInfo[0]);
        }
//        $d_factor = $max_w/1236;
        $d_factor = $max_w/696;

        /** @var Section $section */
        foreach ($sections as $section) {
            $section_text = "Section {$section->name}, id: {$section->id}, fattore divisione: $d_factor";
            // 2 - divido questo max per 1236 ed ottengo un fattore per cui dovrò andare a dividere la W (in realtà divido per il fattore * 2) di tutte le altre section per ottenere la dimensione corretta
            // 3 - passo il fattore ottenuto alla drawOverviewImageWithTaskPoints
            $section->drawOverviewImageWithTaskPoints($task_ids, $d_factor);
            $overview_img = $section->getPointsImageOverview();
            $html .= <<<EOF
                    <!-- <tr>
                        <td width="696" style="border: 1px solid #ececec">
                            $section_text
                        </td>
                    </tr> -->
                    <tr>
                        <td width="696" style="border: 1px solid #ececec">
                            <img width="928" src="file://$overview_img" alt="Section Overview Image">
                        </td>
                    </tr>
EOF;
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Associate the "corrosion_map" Template and its Placeholders to an object
     */
    public function setupCorrosionMapTemplate()
    {
        $category = $this->persistAndAssignTemplateCategory('corrosion_map');
        $placeholders = [
            '$date$' => 'currentDate()',
            '$boat_type$' => 'getBoatType()',
            '$boat_name$' => 'getBoatName()',
            '$break_n1$' => null,  // riconosciuto dal sistema
            '$html_bloccoTask$' => 'getCorrosionMapHtmlBlock()',
            '$html_sectionImgsOverview$' => 'getCorrosionMapHtmlSectionImgsOverview()',
            '$html_tableOfContents$' => 'getCorrosionMapHtmlTableOfContents()'
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
     * Associate the "environmental_report" Template and its Placeholders to an object
     */
    public function setupEnvironmentalReportTemplate()
    {
        $category = $this->persistAndAssignTemplateCategory('environmental_report');
        $placeholders = [
            '$date$' => 'currentDate()',
            '$boat_type$' => 'getBoatType()',
            '$boat_name$' => 'getBoatName()',
            '$data_source$' => 'getCurrentDataSource()',
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
        $this->insertPlaceholders('environmental_report', $placeholders, true);
    }

    public function handlePhpdocxBlockCloning()
    {

    }

    /**
     * @param $date_start
     */
    public function setCurrentDateStart($date_start)
    {
        $this->_current_date_start = $date_start;
    }

    /**
     * @param $date_end
     */
    public function setCurrentDateEnd($date_end)
    {
        $this->_current_date_end = $date_end;
    }

    /**
     * @param $min_tresholds
     */
    public function setCurrentMinThresholds($min_tresholds)
    {
        $this->_current_min_tresholds = $min_tresholds;
    }

    /**
     * @param $data_source
     */
    public function setCurrentDataSource($data_source)
    {
        $this->_current_data_source = $data_source;
    }

    /**
     * @return string
     */
    public function getCurrentDataSource()
    {
        return $this->_current_data_source;
    }

    /**
     * Insert a chart with measurement values for a specific param
     *
     * @param CreateDocxFromTemplate $template_processor
     * @param string $chart_name
     * @param string $param_key
     * @param string $legend
     * @param string $color
     * @throws \Throwable
     */
    public function handleEnvironmentalParamChart(CreateDocxFromTemplate &$template_processor, string $chart_name, string $param_key, string $legend, string $color = '4')
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {

            $data_source = $this->_current_data_source;
            throw_if(!$data_source, new \Exception("Mandatory parameter 'data_source' is missing!", 403));

            $uom = $env_param->unity_of_measure;
            $min_threshold = isset($this->_current_min_tresholds[$env_param->name]) ? $this->_current_min_tresholds[$env_param->name] : null;

            $data = [
                'legend' => $min_threshold ? ["Min Threshold - ($min_threshold $uom)", "$legend ($uom)"] : ["$legend ($uom)"],
            ];

            $i = 0;
            $hax_print_step = 10;

            if ($this->_current_date_start && $this->_current_date_end) {
                $measurements = $env_param->getMeasurementsInRange($this->_current_date_start, $this->_current_date_end, $data_source);
                $max_scale = $env_param->getMaximumInRange($this->_current_date_start, $this->_current_date_end, $data_source);
                $min_scale = $min_threshold ? min($min_threshold, $env_param->getMinimumInRange($this->_current_date_start, $this->_current_date_end, $data_source)) :
                    $env_param->getMinimumInRange($this->_current_date_start, $this->_current_date_end, $data_source);

            } else {
                $measurements = $env_param->measurements;
                $max_scale = $env_param->getMaximum();
                $min_scale = $min_threshold ? min($min_threshold, $env_param->getMinimum()) : $env_param->getMinimum();
            }
            $measurements_num = count($measurements);

            throw_if(!$measurements_num, new \Exception("No data in this date range, for this data source!", 403));

            /** @var Measurement $measurement */
            foreach ($measurements as $measurement) {
                $step = ++$i % $hax_print_step;
                $data['data'][] =
                    [
                        'name' => ($step == 0) ? $measurement->measurement_time : '',
                        'values' => $min_threshold ? [$min_threshold, $measurement->measured_value] : [$measurement->measured_value]
                    ];
            }

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
                'vaxLabel' => $vax_label,
                'haxLabelDisplay' => 'vertical',
                'vaxLabelDisplay' => 0,
                'hgrid' => '1',
                'vgrid' => $measurements_num > 100 ? 0 : '1',
                'scalingMax' => $max_scale,
                'scalingMin' => $min_scale,
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
     * @throws \Throwable
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

    /**
     * @param $param_key
     * @return mixed
     */
    public function getEnvironmentalParamFirstMeasureDate($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            if ($this->_current_date_start && $this->_current_date_end) {
                return $env_param->getMinTimeInRange($this->_current_date_start, $this->_current_date_end, $this->_current_data_source);
            } else {
                return $env_param->getMinTime($this->_current_data_source);
            }
        }
    }

    /**
     * @param $param_key
     * @return mixed
     */
    public function getEnvironmentalParamLastMeasureDate($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            if ($this->_current_date_start && $this->_current_date_end) {
                return $env_param->getMaxTimeInRange($this->_current_date_start, $this->_current_date_end, $this->_current_data_source);
            } else {
                return $env_param->getMaxTime($this->_current_data_source);
            }
        }
    }

    /**
     * @param $param_key
     * @return mixed
     */
    public function getEnvironmentalParamMax($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            if ($this->_current_date_start && $this->_current_date_end) {
                return $env_param->getMaximumInRange($this->_current_date_start, $this->_current_date_end, $this->_current_data_source);
            } else {
                return $env_param->getMaximum($this->_current_data_source);
            }
        }
    }

    /**
     * @param $param_key
     * @return mixed
     */
    public function getEnvironmentalParamMin($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            if ($this->_current_date_start && $this->_current_date_end) {
                return $env_param->getMinimumInRange($this->_current_date_start, $this->_current_date_end, $this->_current_data_source);
            } else {
                return $env_param->getMinimum($this->_current_data_source);
            }
        }
    }

    /**
     * @param $param_key
     * @return mixed
     */
    public function getEnvironmentalParamAvg($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            if ($this->_current_date_start && $this->_current_date_end) {
                return $env_param->getAverageInRange($this->_current_date_start, $this->_current_date_end, $this->_current_data_source);
            } else {
                return $env_param->getAverage($this->_current_data_source);
            }
        }
    }

    /**
     * @param $param_key
     * @return mixed
     */
    public function getEnvironmentalParamStd($param_key)
    {
        /** @var EnvironmentalParameter $env_param */
        $env_param = $this->retrieveEnvironmentalParameterByKey($param_key);
        if ($env_param) {
            if ($this->_current_date_start && $this->_current_date_end) {
                return $env_param->getStandardDeviationInRange($this->_current_date_start, $this->_current_date_end, $this->_current_data_source);
            } else {
                return $env_param->getStandardDeviation($this->_current_data_source);
            }
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
