<?php

namespace App\Traits;

use App\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Str;
use Net7\DocsGenerator\Utils;
use Net7\EnvironmentalMeasurement\Models\EnvironmentalParameter;
use Net7\EnvironmentalMeasurement\Models\Measurement;
use Phpdocx\Create\CreateDocxFromTemplate;
use Phpdocx\Elements\WordFragment;
use function array_slice;
use function count;
use function date;
use function time;

trait EnvParamsInputOutputTransations
{

    /**
     * Override the function on HasMeasurements Trait
     *
     * @param $measurements
     * @param null $source
     *
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
     *
     */
    public function translateMeasurementsInput($measurements, $source = null)
    {
        foreach ($measurements as $measurement_array) {
            $time = $measurement_array['Time'];
            foreach (array_slice($measurement_array, 2, 3) as $param_name_uom_noutf8 => $value) {
                $param_name_uom = utf8_encode($param_name_uom_noutf8);
                $param_name = Str::before($param_name_uom, '(');
                $uom = Str::before(Str::after($param_name_uom, '('), ')');
                $this->addMeasurement($param_name, $value, $time, $uom, $source);
            }
        }
    }

}
