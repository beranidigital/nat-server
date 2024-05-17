<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use App\Models\Carbon;

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
            'min' => 22,
            'max' => 26,
            'score' => 0.4
        ],
        [
            'sensor' => 'temp',
            'min' => 18,
            'max' => 29,
            'score' => 0.7
        ],
        [
            'sensor' => 'ph',
            'min' => 7.2,
            'max' => 8.0,
            'score' => 0.4
        ],
        [
            'sensor' => 'ph',
            'min' => 6.5,
            'max' => 8.6,
            'score' => 0.7
        ],
        [
            'sensor' => 'orp',
            'min' => 700,
            'max' => 750,
            'score' => 0.4
        ],
        [
            'sensor' => 'orp',
            'min' => 650,
            'max' => 700,
            'score' => 0.7
        ],
        [
            'sensor' => 'ec',
            'min' => 2.5,
            'max' => 3.0,
            'score' => 0.4
        ],
        [
            'sensor' => 'ec',
            'min' => 2.0,
            'max' => 2.5,
            'score' => 0.7
        ],
        [
            'sensor' => 'tds',
            'min' => 0,
            'max' => 500,
            'score' => 1.0
        ],
        [
            'sensor' => 'tds',
            'min' => 0,
            'max' => 600,
            'score' => 0.7
        ],
        [
            'sensor' => 'tds',
            'min' => 0,
            'max' => 750,
            'score' => 0.5
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
                $device['😎'] = $states[0];
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

        $formattedValue = number_format($value, 2, '.', '');

        return [
            'value' => $formattedValue,
            'unit' => '°C',
            'label' => __('translation.temp'),
        ];
    }

    public static function formatPH($value)
    {
        return [
            'value' => $value,
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
            'unit' => 'μS/cm',
            'label' => __('translation.ec'),
        ];
    }

    public static function formatTDS($value)
    {
        return [
            'value' => intval($value),
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
     * @param array<string, array> $state // e.g ['temperature' => ['value' => 30.0, 'unit' => '°C']]
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
