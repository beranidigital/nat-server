<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;

class WaterpoolController extends Controller
{

    public static function getStates(string $deviceName = null, int $limit = 15): array
    {
        if ($deviceName == null) $deviceName = AppSettings::$natwaveDevices[0];
        $datas = SensorDataController::getStats($deviceName, $limit);

        $sensors = [];
        for ($i = 0; $i < $limit; $i++) {
            $state = [];
            $averageTimestamp = 0;
            $latestTimestamp = 0;

            foreach ($datas as $sensor => $data) {
                if (!isset($data['data'][$i])) {
                    $state[$sensor] = 0.0;
                } else {
                    $state[$sensor] = $data['data'][$i];
                }
                $averageTimestamp += isset($data['timestamp'][$i]) ? strtotime($data['timestamp'][$i]) : 0;
                $latestTimestamp = max($latestTimestamp, isset($data['timestamp'][$i]) ? strtotime($data['timestamp'][$i]) : 0);
            }

            if (count($datas) == 0) continue;
            $averageTimestamp /= count($datas);
            $state['timestamp'] = $averageTimestamp;
            $state['latestTimestamp'] = $latestTimestamp; // in here as not to fuck the array
            $sensors[] = $state;
        }


        return $sensors;
    }

    public static function formatStates(array $states): array
    {
        $formattedStates = [];

        foreach ($states as $state) {
            $formattedState = [];
            foreach ($state as $sensorOrEntityName => $value) {
                $sensor = AppSettings::entityToSensorName($sensorOrEntityName);
                $formattedState[$sensor] = self::formatSensor($sensor, $value);
            }
            $formattedStates[] = $formattedState;
        }
        return $formattedStates;
    }


    public static function formatSensor(string $sensor, $value)
    {
        //if ($sensor == 'timestamp' || $sensor == 'latestTimestamp') return date('Y-m-d H:i:s', $value);
        //if (in_array($sensor, AppSettings::$ignoreSensors)) return $value;


        $sensor_name = AppSettings::entityToSensorName($sensor);


        switch ($sensor_name) {
            case 'ec':
                return StatusController::formatConductivity($value);
            case 'humid':
                return StatusController::formatSalt($value);
            case 'orp':
                return StatusController::formatORP($value);
            case 'ph':
                return StatusController::formatPH($value);
            case 'tds':
                return StatusController::formatTDS($value);
            case 'temp':
                return StatusController::formatTemperature($value);
            case 'cl':
                return StatusController::formatChlorine($value);
            case 'battery':
                return StatusController::formatBattery($value);
            default:
                //throw new \Exception("Unknown sensor: {$sensor_name}");
                //Log::warning("Unknown sensor: {$sensor_name}");
                // if(config('app.debug')) throw new \Exception("Unknown sensor: {$sensor_name}");

                return [
                    'value' => $value,
                    'unit' => '',
                    'label' => __('translation.' . $sensor_name),
                ];
        }
    }

    public static function calculateTDS(mixed $ec)
    {
        if($ec == 'unknown' || $ec == 'unvailable'){
            return $ec;
        }
        $ec = floatval($ec);
        return $ec * 0.5;
    }


    public static $allowedSensors = ['tds', 'cl', 'ph', 'ec', 'temp', 'orp'];

    public static function getAllowedSensors(): array
    {
        return self::$allowedSensors;;
    }
}
