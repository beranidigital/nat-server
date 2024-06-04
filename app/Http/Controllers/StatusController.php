<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;

class StatusController extends Controller
{


    // Threshold for each parameter
    // Example sensor 1
    // if range of 28< or >20 get score 1
    // else if range of 30< or >19 get score 0.7
    // else 0.5

    // Evaluated from top to bottom
    // 3 = green
    // 2 = yellow
    // integer will be automatically converted to float
    public static $parametersThresholdInternational = [
        [
            'sensor' => 'temp',
            'start' => '0',
            'cs' => '18',
            'gs' => '22',
            'ge' => '26',
            'ce' => '28',
            'end' => '50'
        ],
        [
            'sensor' => 'ph',
            'start' => '0',
            'cs' => '6.5',
            'gs' => '7.2',
            'ge' => '8.0',
            'ce' => '10.0',
            'end' => '10.0'
        ],
        [
            'sensor' => 'orp',
            'start' => '100',
            'cs' => '650',
            'gs' => '700',
            'ge' => '750',
            'ce' => '800',
            'end' => '800'
        ],
        [
            'sensor' => 'ec',
            'start' => '1.0',
            'cs' => '2.0',
            'gs' => '2.5',
            'ge' => '3.0',
            'ce' => '4.0',
            'end' => '5.0'
        ],
        [
            'sensor' => 'tds',
            'start' => '0',
            'cs' => '200',
            'gs' => '700',
            'ge' => '1500',
            'ce' => '2000',
            'end' => '2000'
        ],
        [
            'sensor' => 'cl',
            'start' => '0',
            'cs' => '0.1',
            'gs' => '1.0',
            'ge' => '3.0',
            'ce' => '5.0',
            'end' => '5.0'
        ],
    ];


    public static $parameterThresholdDisplay = [
    ];
    public static $finalScoreDisplay = [
    ];

    public function index()
    {

        // internal name => display name
        $devices = [

        ];
        foreach (AppSettings::getDevicesName()->value as $id => $name) {
            $devices[$id] = $name;

        }

        $data = [
            'devices' => [
            ]
        ];

        foreach ($devices as $deviceName => $deviceDisplayName) {
            $device = [
                'name' => $deviceName,
                'display_name' => $deviceDisplayName,
                'state' => $this->getState($deviceName),
            ];

            $device['scores'] = $this->calculateScore($device['state'], $deviceName);

            $device['final_score'] = $this->calculateFinalScore($device['scores'], $deviceName);
            $states = WaterpoolController::getStates($deviceName, 1);
            if (count($states) != 0) {
                $device['ðŸ˜Ž'] = $states[0];
            }
            $data['devices'][] = $device;
        }



        $data['parameterThresholdDisplay'] = self::$parameterThresholdDisplay;
        $data['finalScoreDisplay'] = self::$finalScoreDisplay;
        return view('dashboards.smart-home', $data);
    }


    protected static function getState($deviceName)
    {
        $data = SensorDataController::getStats($deviceName, 1);
        $result = [];

        foreach ($data as $key => $value) {
            $sensorName = AppSettings::entityToSensorName($key);
            $result[$sensorName] = $value['format'];

        }
        return $result;
    }

    public static function formatTemperature($value)
    {

        $formattedValue = $value;
        return [
            'value' => $formattedValue,
            'unit' => '°C',
            'label' => __('translation.temp'),
        ];
    }

    public static function formatPH($value)
    {
        $numericValue = floatval($value) / 10;
        return [
            'value' => strval($numericValue),
            'unit' => 'pH',
            'label' => __('translation.ph'),
        ];
    }

    // allow for multiple devices

    public static function formatSalt($value)
    {
        return [
            'value' => $value,
            'unit' => 'mg/l',
            'label' => __('translation.humid'),
        ];
    }

    public static function formatORP($value)
    {
        return [
            'value' => $value,
            'unit' => 'mV',
            'label' => __('translation.orp'),
        ];
    }

    public static function formatConductivity($value)
    {
        return [
            'value' => $value,
            'unit' => 'uS/cm',
            'label' => __('translation.ec'),
        ];
    }

    public static function formatTDS($value)
    {
        return [
            'value' => $value,
            'unit' => 'ppm',
            'label' => __('translation.tds'),
        ];
    }

    public static function formatChlorine($value)
    {
        return [
            'value' => $value,
            'unit' => 'mg/l',
            'label' => __('translation.cl'),
        ];
    }

    public static function formatBattery($value)
    {

        return [
            'value' => $value,
            'unit' => '%',
            'label' => __('translation.battery'),
        ];
    }
    /**
     * Calculate final score from all parameters
     * @param array $scores
     * @return float
     */
    public static function calculateFinalScore(array $scores): float
    {

        // calculate based PH and ORP
        $ph = $scores['ph'] ?? 0;
        $orp = $scores['orp'] ?? 0;
        return $ph * $orp;

    }

    /**
     * Calculate score for each parameter
     * @param array<string, array> $state // e.g ['temperature' => ['value' => 30.0, 'unit' => 'Â°C']]
     * @return array<string, float> $scores // e.g ['temperature' => 1.0]
     */
    public static function calculateScore(array $state, string $deviceName): array
    {
        $scores = [];
        foreach ($state as $sensor => $value) {
            $value = floatval($value['value'] ?? 0);
            $scores[$sensor] = self::calculateScoreFor($sensor, $value, $deviceName);
        }

        return $scores;
    }

    /**
     * Calculate score for sensor
     * @param string $sensor entity name of sensor
     * @param float $value any value
     * @return float 0.0 - 1.0
     */
    public static function calculateScoreFor(string $sensor, float $value, string $deviceName): float
    {
        return SensorDataController::calculateScoreFor($sensor, $value, $deviceName);
    }


}

StatusController::$parameterThresholdDisplay = [
    'green' => AppSettings::$greenScoreMin,
    'yellow' => AppSettings::$yellowScoreMin,
];

StatusController::$finalScoreDisplay = [
    'green' => AppSettings::$greenScoreMin,
    'yellow' => AppSettings::$yellowScoreMin,
];
