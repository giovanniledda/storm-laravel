<?php

namespace App\Traits;

use App\User;
use Exception;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Net7\DocsGenerator\Utils;
use Net7\EnvironmentalMeasurement\Models\EnvironmentalParameter;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use Phpdocx\Create\CreateDocxFromTemplate;
use Phpdocx\Elements\WordFragment;
use function array_slice;
use function count;
use function date;
use function throw_if;
use function time;

trait EnvParamsInputOutputTranslations
{

    /**
     * Override the function on HasMeasurements Trait
     *
     * @param $measurements
     * $array ===> array:169 [▼
     * 0 => array:6 [▼
     * "FB 272 SD EXT" => "1"
     * "Time" => "2017-12-05 09:31:14"
     * b"Celsius(°C)" => "20.5"
     * "Humidity(%rh)" => "33.0"
     * b"Dew Point(°C)" => "3.7"
     * "Serial Number" => "010095858"
     * ],
     * 1 => array:6 [▼
     * "FB 272 SD EXT" => "2"
     * "Time" => "2017-12-05 10:31:14"
     * b"Celsius(°C)" => "21.0"
     * "Humidity(%rh)" => "30.5"
     * b"Dew Point(°C)" => "3.0"
     * "Serial Number" => "-"
     * ],
     * ...
     * @param null $source
     * @param null $document
     * @param array $min_thresholds
     */
    public function translateMeasurementsInputForTempDPHumSensor($measurements, $source = null, $document = null  , $min_thresholds = [])
    {
        $array_ok = false;
        foreach ($measurements as $measurement_array) {
            // TODO: rimettere il controllo, ora non funge perché il carattere ° è corrotto
//            if (!$array_ok) {
//                if (!$this->checkTempDPHumSensorLogFileStandardCompliance($measurement_array)) {
//                    throw new \Exception('The log file is not compliant!');
//                }
//                $array_ok = true;
//            }
            $time = $measurement_array['Time'];
            foreach (array_slice($measurement_array, 2, 3) as $param_name_uom_noutf8 => $value) {
                $param_name_uom = utf8_encode($param_name_uom_noutf8);  // sistema i caratteri corrotti
                $param_name = Str::before($param_name_uom, '(');
                $uom = Str::before(Str::after($param_name_uom, '('), ')');
                $min_threshold = isset($min_thresholds[$param_name]) ? $min_thresholds[$param_name] : null;
                $this->addMeasurement($param_name, $value, $time, $uom, $source, $min_threshold, $document);
            }
        }
    }

    /**
     * @param $measurement_array
     * @return bool
     */
    public function checkTempDPHumSensorLogFileStandardCompliance($measurement_array)
    {
        return Arr::has($measurement_array, ['Time', 'Celsius(°C)', 'Humidity(%rh)', 'Dew Point(°C)', 'Serial Number']);
    }

}
