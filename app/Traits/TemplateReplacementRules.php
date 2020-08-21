<?php
namespace App\Traits;

use App\ApplicationLog;
use App\ApplicationLogSection;
use App\DetectionsInfoBlock;
use App\GenericDataInfoBlock;
use App\Product;
use App\ProductUseInfoBlock;
use App\Section;
use App\Task;
use App\Tool;
use App\Zone;
use App\ZoneAnalysisInfoBlock;
use Illuminate\Support\Facades\Storage;
use Net7\DocsGenerator\Utils;
use Net7\Documents\Document;
use Net7\EnvironmentalMeasurement\Models\EnvironmentalParameter;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use Phpdocx\Create\CreateDocxFromTemplate;
use Phpdocx\Elements\WordFragment;

use function array_reduce;
use function array_walk;
use function ceil;
use function count;
use function date;
use function factory;
use function fclose;
use function foo\func;
use function getimagesize;
use function logger;
use function min;
use function strtotime;
use function throw_if;
use function time;
use function trim;
use function unlink;

use const APPLICATION_TYPE_COATING;
use const APPLICATION_TYPE_FILLER;
use const APPLICATION_TYPE_HIGHBUILD;
use const APPLICATION_TYPE_PRIMER;
use const APPLICATION_TYPE_UNDERCOAT;

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

    // Usate con il DocsGenerator: per application_log_report
    protected $_current_app_log;


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
        $toc_pages = ceil(count($tasks)/26);
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
                    <tr style="height: 32px">
                        <td width="496" style="border-bottom: 1px solid #ececec;"><b>Point #$point_id</b> ($task_location)</td>
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
        $html = '<div>';
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
        $d_factor = $max_w/2198;
//        $d_factor = $max_w/1236;
//        $d_factor = $max_w/696;

        /** @var Section $section */
        foreach ($sections as $section) {
            $section_text = "{$section->name}";
            // 2 - divido questo max per 1236 ed ottengo un fattore per cui dovrò andare a dividere la W (in realtà divido per il fattore * 2) di tutte le altre section per ottenere la dimensione corretta
            // 3 - passo il fattore ottenuto alla drawOverviewImageWithTaskPoints
            $section->drawOverviewImageWithTaskPoints($task_ids, $d_factor);
            $overview_img = $section->getPointsImageOverview();
            $html .= <<<EOF

                    <img width="926" align="center" src="file://$overview_img" alt="Section Overview Image">

                    <p style="text-align:center; color: #999999">$section_text</p>

EOF;
        }
        $html .= '</div>';
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



    /**
     * *****************************
     * *****************************  TEMPLATE: application_log_report
     * *****************************
     *
     */

    /**
     * Associate the "application_log_report" Template and its Placeholders to an object
     */
    public function setupApplicationLogTemplate()
    {
        $category = $this->persistAndAssignTemplateCategory('application_log_report');
        $placeholders = [
            '$date$' => 'currentDate()',
            '$name$' => 'getCurrentAppLogName()',
            '$typeOfAppReport$' => 'getCurrentAppLogType()',
            '$zones$' => 'getCurrentAppLogZones()',
            '$break_n1$' => null,  // riconosciuto dal sistema
            '$html_fullApplicationLog$' => 'getCurrentAppLogStructureHtml()',
        ];
        $this->insertPlaceholders('environmental_report', $placeholders, true);
    }

    /**
     * @param $app_log
     */
    public function setCurrentAppLog($app_log)
    {
        $this->_current_app_log = $app_log;
    }

    /**
     * @return ApplicationLog
     */
    public function getCurrentAppLog()
    {
        return $this->_current_app_log ?? $this->application_logs()->first();
    }

    /**
     * @return string
     */
    public function getCurrentAppLogName()
    {
        return $this->getCurrentAppLog()->name;
    }

    /**
     * @return string
     */
    public function getCurrentAppLogType()
    {
        return $this->getCurrentAppLog()->application_type;
    }

    /**
     * @return string
     *
     * Ex:. Zone name 1A; Zone long name 3A; Zone name 4C; Zone another long name 5D
     */
    public function getCurrentAppLogZones()
    {
        $zones_str = '';
        $app_log = $this->getCurrentAppLog();
        $zones_section = $app_log->getZonesSection();
        if ($zones_section) {
            $zone_ib = $zones_section->zone_analysis_info_blocks;
            /** @var ZoneAnalysisInfoBlock $item */
            foreach ($zone_ib as $item) {
                /** @var Zone $zone */
                $zones_str .= $item->zone->description.' '.$item->zone->code.',';
            }
        }
        return trim($zones_str, ',');
    }

    /**
     * @param $photos_paths
     * @return string
     */
    public function renderPhotosBlock($photos_paths)
    {
        return count($photos_paths) > 1 ?
            '<tr height="190">
	                <td width="340" style=""><img height="255" src="'.$photos_paths[0].'"></td>
	                <td width="16" style=""></td>
	                <td width="340" style=""><img height="255" src="'.$photos_paths[1].'"></td>
	         </tr>
	         <tr style="height: 32px;"><td width="696"></td></tr>'
            :
            '<tr height="190">
	                <td width="340" style=""><img height="255" src="'.$photos_paths[0].'"></td>
	         </tr>
	         <tr style="height: 32px;"><td width="696"></td></tr>';
    }

    /**
     * @param $photos
     * @return string
     */
    public function renderPhotos($photos)
    {
        $html = '';
        if (!empty($photos) && !empty($photos['data']['detailed_images'])) {
            $det_imgs = $photos['data']['detailed_images'];
            $counter = 0;
            $photos_paths = [];
            foreach ($det_imgs as $key => $det_img) {
                $photos_paths[$counter] = 'file://'.$det_img['attributes']['file_path'];
                if ($counter == 1 || $key === array_key_last($det_imgs)) {
                    $html .= $this->renderPhotosBlock($photos_paths);
                    $counter = 0;
                    $photos_paths = [];
                    continue;
                }
                $counter++;
            }
        }
        return $html;
    }

    /**
     * @param $photos_paths
     * @return string
     */
    public function renderDetectionBlock($detection_values)
    {
        return count($detection_values) > 1 ?
            '<tr width="696" height="190">
                <td width="340" style=""><img height="255" src="'.$detection_values[0]['file_path'].'"></td>
                <td width="16" style=""></td>
                <td width="340" style=""><img height="255" src="'.$detection_values[1]['file_path'].'"></td>
            </tr>
            <tr width="696">
                <td width="340" style="">'.$detection_values[0]['det_value'].'</td>
                <td width="16" style=""></td>
                <td width="340" style="">'.$detection_values[1]['det_value'].'</td>
            </tr>
            <tr style="height: 32px"><td width="696"></td></tr>'
            :
            '<tr width="340" height="190">
                <td width="340" style=""><img height="255" src="'.$detection_values[0]['file_path'].'"></td>
            </tr>
            <tr width="340">
                <td width="340" style="">'.$detection_values[0]['det_value'].'</td>
            </tr>
            <tr style="height: 32px;"><td width="696"></td></tr>';
    }

    /**
     * @param DetectionsInfoBlock $detections_ib
     * @param $detection_param_keys
     * @return string
     */
    public function renderDetections(DetectionsInfoBlock &$detections_ib, $detection_param_keys) // 'surface_roughness', 'salts', 'other...
    {
        $html = '';
        $detections_array = $detections_ib->detections;
        if (!empty($detections_array)) {
            $counter = 0;
            $detection_values = [];
            foreach ($detections_array as $key => $detection) {
                $image_doc = Document::find($detection['image_doc_id']);
                if ($image_doc) {
                    $image_json = $detections_ib->extractJsonDocumentPhotoInfo($image_doc);
                    $detection_values[$counter]['file_path'] = 'file://'.$image_json['attributes']['file_path'];
                } else {
                    $placeholder_url = Storage::disk('public')->url('placeholder150.png');
                    $detection_values[$counter]['file_path'] = 'file://'.$placeholder_url;
                }
                $val = '';
                foreach ($detection_param_keys as $detection_param_key) {
                    $val .= $detection_param_key.': '.$detection[$detection_param_key].', ';
                }
                $detection_values[$counter]['det_value'] = trim($val, ', ');
                if ($counter == 1 || $key === array_key_last($detections_array)) {
                    $html .= $this->renderDetectionBlock($detection_values);
                    $counter = 0;
                    $detection_values = [];
                    continue;
                }
                $counter++;
            }
        }
        return $html;
    }

    /**
     * @param DetectionsInfoBlock $detection_info_block
     * @param $block_title
     * @param $detection_param_keys
     * @return string
     */
    public function renderRegularDetectionInfoBlock(DetectionsInfoBlock &$detection_info_block, $block_title, $detection_param_keys)
    {
        // come nascondere blocchi -> se le detections sono vuote, non stampo nulla
        $detections_array = $detection_info_block->detections;
        if (!empty($detections_array)) {
            foreach ($detections_array as $key => $detection) {
                foreach ($detection_param_keys as $detection_param_key) {
                    if (empty($detection[$detection_param_key])) {
                        return '';
                    }
                }
            }
        }
        $short_description = $detection_info_block->short_description;
        /** @var Tool $tool */
        $tool = $detection_info_block->tool;
        $tool_name = $tool ? $tool->name : '-';
        $tool_exp_date = $tool ? $tool->calibration_expiration_date : '-';

        $html = <<<EOF
	    <table width="340" cellpadding="0" cellspacing="0">
	        <tbody>
	            <tr style="height: 32px">
	                <td width="696" style="font-weight: 700; color: black">$block_title</td>
	            </tr>
	            <tr style="height: 32px">
	                <td width="696" style="">Tool: $tool_name</td>
	            </tr>
	            <tr style="height: 32px">
	                <td width="696" style="">Tool expiration date: $tool_exp_date</td>
	            </tr>
	            <tr style="height: 32px">
	                <td width="696" style="">Description: $short_description</td>
	            </tr>
	        </tbody>
	    </table>
EOF;

        $html .= <<<EOF
	    <table cellpadding="0" cellspacing="0">
	        <tbody>
EOF;
        $html .= $this->renderDetections($detection_info_block, $detection_param_keys);
        $html .= <<<EOF
	        </tbody>
	    </table>
    <p style="page-break-before: always;"></p>
EOF;
        return $html;
    }

    /**
     * @return string
     */
    public function renderPreparationSection()
    {
        $application_log = $this->getCurrentAppLog();
        /** @var ApplicationLogSection $preparation_section */
        $preparation_section = $application_log->getPreparationSection();
        $date = date('d/m/Y', strtotime($preparation_section->date_hour));

        $html = <<<EOF
            <p style="page-break-before: always;"></p>
            <p style="text-align: center;font-size: 21px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Surface Preparation</p>

            <table cellpadding="0" cellspacing="0">
                <tbody>
                    <tr style="height: 32px">
                        <td width="696" style="font-weight: 700; color: black">Date</td>
                    </tr>
                    <tr style="height: 32px">
                        <td width="696" style="">$date</td>
                    </tr>
                    <tr style="height: 32px"><td width="696"></td></tr>
                </tbody>
            </table>
EOF;

        /** @var ProductUseInfoBlock $substrate */
        $substrate = $preparation_section->product_use_info_blocks()->first();
        $product_name = $substrate ? ($substrate->product ? $substrate->product->name : '-') : '-';

        /** @var GenericDataInfoBlock $surface_preparation */
        $surface_preparation = $preparation_section->generic_data_info_blocks()->first();
        $kv_infos = $surface_preparation->key_value_infos;
        $paper_grain = $short_desc = '-';
        if (!empty($kv_infos)) {
            $paper_grain = $kv_infos['paper_grain'] ?? '-';
            $short_desc = $kv_infos['short_description'] ?? '-';
        }

        // Substrate preparation
        $html .= <<<EOF
	    <table cellpadding="0" cellspacing="0">
	        <tbody>
	            <tr style="height: 32px">
	                <td width="696" style="font-weight: 700; color: black">Preparation of substrate</td>
	            </tr>
	            <tr style="height: 32px">
	                <td width="696" style="">Visual inspection of surfaces</td>
	            </tr>
EOF;

        // Immagini
        $photos = $surface_preparation->getPhotosApi(); // detailed_images + additional_images
        $html .= $this->renderPhotos($photos);

        $html .= <<<EOF
	            <tr style="height: 32px">
	                <td width="696" style="">Substrate product: $product_name</td>
	            </tr>
	            <tr style="height: 32px">
	                <td width="696" style="">Paper grain: $paper_grain</td>
	            </tr>
	            <tr style="height: 32px">
	                <td width="696" style="">Description: $short_desc</td>
	            </tr>
	        </tbody>
	    </table>
	    <p style="page-break-before: always;"></p>
EOF;

        // Surface inspection
        /** @var DetectionsInfoBlock $surface_inspection */
        $surface_inspection = $preparation_section->getSurfaceInspectionDetectionBlock();
        $html .= $this->renderRegularDetectionInfoBlock($surface_inspection, 'Roughness', ['surface_roughness']);

        // Salt (Il cliente lo chiama "Bresle Test": https://en.wikipedia.org/wiki/Bresle_method)
        if ($application_log->application_type == APPLICATION_TYPE_PRIMER) {
            /** @var DetectionsInfoBlock $salt */
            $salt = $preparation_section->getSaltDetectionBlock();
            $html .= $this->renderRegularDetectionInfoBlock($salt, 'Salt', ['salts']);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function renderApplicationSection()
    {
        $application_log = $this->getCurrentAppLog();
        /** @var ApplicationLogSection $application_section */
        $application_section = $application_log->getApplicationSection();
        $date_hour = date('d/m/Y H:i', strtotime($application_section->date_hour));

        $html = <<<EOF
                <p style="text-align: center;font-size: 21px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Product Application</p>

                <!-- Application date and hour -->
                <table cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr style="height: 32px">
                            <td width="696" style="font-weight: 700; color: black">Application date and hour</td>
                        </tr>

                        <tr style="height: 32px">
                            <td width="696" style="">$date_hour</td>
                        </tr>

                        <tr style="height: 32px"><td width="696"></td></tr>
                    </tbody>
                </table>
EOF;

        /** @var DetectionsInfoBlock $temp_hum */
        $temp_hum = $application_section->getTemperatureAndHumidityDetectionBlock();
        $html .= '<p style="font-weight: bold; color: black">AMBIENT CONDITIONS</p>'.
            $this->renderRegularDetectionInfoBlock($temp_hum, 'Temperature & humidity', ['temperature', 'humidity']);

        /** @var ProductUseInfoBlock $application_product */
        $application_product = $application_section->product_use_info_blocks()->first();

        /** @var Product $product */
        $product = $application_product->product;
        $product_name = $product->name;
        $sv_percentage = $product->sv_percentage;
        $viscosity = $application_product->viscosity;
        $diluition = 0;  // somma litri*barattoli dei thinner diviso somma dei litri*barattoli dei componenti

        // Product applied
        $html .= <<<EOF
            <table cellpadding="0" cellspacing="0">
                <tbody>
                    <tr style="height: 32px">
                        <td width="696" style="font-weight: 700; color: black">Product applied</td>
                    </tr>

                    <tr style="height: 32px">
                        <td width="696" style="">$product_name</td>
                    </tr>

                    <tr style="height: 32px"><td width="696"></td></tr>
                </tbody>
            </table>
EOF;

        // Components & thinners
        $html .= <<<EOF
            <table cellpadding="0" cellspacing="0">
                <tbody>
                    <tr style="height: 32px">
                        <td width="348" style="font-weight: 700; color: black">Components & thinners</td>
                        <td width="348" style="font-weight: 700; color: black">Batch number</td>
                    </tr>
EOF;

        $components_liters_sum = 0;
        foreach ($application_product->components as $component) {
            $components_liters_sum += ($component['tins_capacity'] * $component['number_of_tins']);
            $batch_nums = '';
            $name = $component['name'];
            array_walk($component['batch_numbers'], function ($val, $key) use (&$batch_nums) {
                 $batch_nums .= "$key: $val, ";
            });
            $batch_nums = trim($batch_nums, ', ');
            $html .= <<<EOF
                <tr style="height: 32px">
                            <td width="696" style="border-bottom: 1px solid #ececec">Comp.: $name</td>
                            <td width="696" style="border-bottom: 1px solid #ececec">$batch_nums</td>
	            </tr>
EOF;
        }

        $thinners_liters_sum = 0;
        foreach ($application_product->thinners as $thinner) {
            $thinners_liters_sum += ($thinner['tins_capacity'] * $thinner['number_of_tins']);
            $batch_nums = '';
            $name = $thinner['name'];
            array_walk($thinner['batch_numbers'], function ($val, $key) use (&$batch_nums) {
                $batch_nums .= "$key: $val, ";
            });
            $batch_nums = trim($batch_nums, ', ');
            $html .= <<<EOF
                <tr style="height: 32px">
                            <td width="696" style="border-bottom: 1px solid #ececec">Thin.: $name</td>
                            <td width="696" style="border-bottom: 1px solid #ececec">$batch_nums</td>
	            </tr>
EOF;
        }

        if ($components_liters_sum) {
            $diluition = ($thinners_liters_sum/$components_liters_sum)*100;
        }

        $html .= <<<EOF
	            <tr style="height: 32px"><td width="696"></td></tr>
	        </tbody>
	    </table>
EOF;

        /** @var GenericDataInfoBlock $application_method */
        $application_method = $application_section->generic_data_info_blocks()->first();
        $kv_infos = $application_method->key_value_infos;
        if (!empty($kv_infos)) {
            $app_method = $kv_infos['method'] ?? '-';
            $nozzle = $kv_infos['nozzle_needle_size'] ?? '-';
            $loss_factor = $kv_infos['loss_factor'] ?? '-';
            // TODO: La diluizione percentuale sarebbe il totale dei litri di "thinner" diviso i litri di pittura totale (quindi la somma dei litri inseriti tra thinner e components)
            $html .= <<<EOF
                <!-- Application method -->
                <table cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr style="height: 32px">
                            <td width="696" style="font-weight: 700; color: black">Application method</td>
                        </tr>

                        <tr style="height: 32px">
                            <td width="696" style="">$app_method</td>
                        </tr>

                        <tr style="height: 32px"><td width="696"></td></tr>
                    </tbody>
                </table>

                <!-- Application details -->
                <table cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr style="height: 32px">
                            <td width="696" style="font-weight: 700; color: black">Application details</td>
                        </tr>

                        <tr style="height: 32px">
                            <td width="696" style="">Product viscosity: $viscosity</td>
                        </tr>

                        <tr style="height: 32px">
                            <td width="696" style="">Diluition %: $diluition</td>
                        </tr>

                        <tr style="height: 32px">
                            <td width="696" style="">Loss factor: $loss_factor</td>
                        </tr>

                        <tr style="height: 32px">
                            <td width="696" style="">Nozzle & needle size: $nozzle</td>
                        </tr>

                        <tr style="height: 32px"><td width="696"></td></tr>
                    </tbody>
                </table>
                <p style="page-break-before: always;"></p>
EOF;
        }

        return $html;
    }

    public function renderInspectionSection()
    {
        $application_log = $this->getCurrentAppLog();
        /** @var ApplicationLogSection $inspection_section */
        $inspection_section = $application_log->getInspectionSection();
        $html = <<<EOF
            <p style="text-align: center;font-size: 21px;font-weight: bold;color: #1f519b;font-family: Raleway, sans-serif;">Inspection</p>
EOF;

        // Adhesion
        if ($application_log->application_type == APPLICATION_TYPE_PRIMER) {
            /** @var DetectionsInfoBlock $adhesion */
            $adhesion = $inspection_section->getAdhesionDetectionBlock();
            $html .= $this->renderRegularDetectionInfoBlock($adhesion, 'Adhesion', ['adhesion']);
        }

        // Thickness
        /** @var DetectionsInfoBlock $thickness */
        $thickness = $inspection_section->getThicknessDetectionBlock();
        $html .= $this->renderRegularDetectionInfoBlock($thickness, 'Thickness', ['thickness']);

        // Fairness
        if (
            $application_log->application_type == APPLICATION_TYPE_FILLER ||
            $application_log->application_type == APPLICATION_TYPE_HIGHBUILD ||
            $application_log->application_type == APPLICATION_TYPE_UNDERCOAT
        ) {
            /** @var DetectionsInfoBlock $adhesion */
            $fairness = $inspection_section->getFairnessDetectionBlock();
            $html .= $this->renderRegularDetectionInfoBlock($fairness, 'Fairness', ['fairness']);
        }

        // Hardness
        if ($application_log->application_type == APPLICATION_TYPE_FILLER) {
            /** @var DetectionsInfoBlock $hardness */
            $hardness = $inspection_section->getHardnessDetectionBlock();
            $html .= $this->renderRegularDetectionInfoBlock($hardness, 'Hardness', ['hardness']);
        }

        // Gloss / DOI / Haze / Rspec
        if ($application_log->application_type == APPLICATION_TYPE_COATING) {
            /** @var DetectionsInfoBlock $gl_do_ha_rs */
            $gl_do_ha_rs = $inspection_section->getGlassDoiHazeRspecDetectionBlock();
            $html .= $this->renderRegularDetectionInfoBlock($gl_do_ha_rs, 'Gloss / DOI / Haze / Rspec', ['gloss', 'doi', 'haze', 'rspec']);
        }

        // Orange Peel
        if ($application_log->application_type == APPLICATION_TYPE_COATING) {
            /** @var DetectionsInfoBlock $orange_peel */
            $orange_peel = $inspection_section->getOrangePeelDetectionBlock();
            $html .= $this->renderRegularDetectionInfoBlock($orange_peel, 'Orange peel', ['orange_peel']);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getCurrentAppLogStructureHtml()
    {
        $preparation_section_html = $this->renderPreparationSection();
        $application_section_html = $this->renderApplicationSection();
        $inspection_section_html = $this->renderInspectionSection();
        $html = <<<EOF
            $preparation_section_html

            $application_section_html

            $inspection_section_html
EOF;
        return $html;
    }

}
