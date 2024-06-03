<?php

namespace App\Models;

use App\Http\Controllers\StatusController;
use App\Models\Pool\StateLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// Cool stuff here
class AppSettings extends Model
{
      use HasFactory;

    public static $natwaveDevices = [
        'nat_01_1',
        'nat_02_1',
        'nat02_2_2',
    ];
    private static $messages;

    protected $fillable = [
        'key',
        'value',
    ];

    public static $greenScoreMax = 0.9;
    public static $greenScoreMin = 0.7;
    public static $yellowScoreMax = 0.69;
    public static $yellowScoreMin = 0.39;
    public static $ignoreSensors = [
        'timestamp',
        'latestTimestamp',
        'batterydevice',
        'battery'
    ];
    public static $sensors = [
        //'cf', // Chlorophyll
        'ph', // PH
        'orp', // Sanitation (ORP)
        'tds', // TDS
        // 'humid', // Salt
        'ec', // Conductivity
        'temp', // Temperature
        'cl',
        // 'salt',
        // 'batterydevice', // Battery
        'battery'
    ];

    public static $batterySensors = [
        'batterydevice',
        'battery'
    ];

    // ph orp tds humid ec temp batterydevice

    // Used by all
    protected $casts = [
        'value' => 'json',
    ];

    /**
     * @param string $entity e.g sensor.natwave_ec
     * @return string e.g ec
     */
    public static function entityToSensorName(string $entity): string
    {
        // check if it has _ in it
        if (!str_contains($entity, '_')) return $entity;
        // get the last part as sensor name
        $parts = explode('_', $entity);
        return $parts[count($parts) - 1];
    }

    public static function getDevicesName()
    {
        if (app()->runningInConsole()) {
            return (object)[
                'value' => [],
                'key' => 'devices_name'
            ];
        }

        $devicesName = self::get('devices_name');
        $default = [];
        $devices = StateLog::getDevices();
        foreach ($devices as $id => $name) {
            $default[$id] = $name;
        }

        if (!$devicesName) {

            $devicesName = self::create([
                'key' => 'devices_name',
                'value' => $default,
            ]);
        }

        $devicesNameValue = self::syncWithDefault($default, $devicesName->value);

        $devicesName->value = $devicesNameValue;
        return $devicesName;
    }

    public static function getDevices(): array
    {
        return StateLog::getDevices();
    }

    public static function translateDeviceName($id)
    {
        return __('devices_name.' . $id);
    }

    public static function translateSensorKey(string $sensor): string
    {
        $sensor = self::entityToSensorName($sensor);
        return __('translation.' . $sensor);
    }

    public static function getTranslation()
    {
        $translation = self::get('translation');
        $default = [
            'ec' => 'Conductivity',
            // 'humid' => 'Salt',
            'orp' => 'Sanitation (ORP)',
            'ph' => 'PH',
            'tds' => 'TDS',
            'temp' => 'Temperature',
            'timestamp' => 'Timestamp',
            "latestTimestamp" => "Latest Timestamp",
        ];
        foreach (self::$sensors as $sensor) {
            if (!isset($default[$sensor])) $default[$sensor] = Str::upper($sensor);
        }

        if (!$translation) {
            $translation = self::create([
                'key' => 'translation',
                'value' => $default,
            ]);
        }
        // check if lacking
        $translationValue = self::syncWithDefault($default, $translation->value);
        $translation->value = $translationValue;

        return $translation;
    }

    public static function getMessage()
    {
        $message = self::get('message');
        $default = self::$message;

        if (!$message) {
            $message = self::create([
                'key' => 'message',
                'value' => $default,
            ]);
        }

        $messageValue = self::syncWithDefault($default, $message->value);
        $message->value = $messageValue;
        return $message;
    }

    public static $message = [
        'good' => 'Good: Water with optimal pH and proper ORP values is considered safe and conducive to health.',
        'caution' => 'Caution: Water with suboptimal pH and ORP values may pose risks to health and water quality.',
        'badOrp' => 'Bad: Water with suboptimal ORP value may pose risks to health and water quality.',
        'badPh' => 'Bad: Water with suboptimal pH value may pose risks to health and water quality.',
        'bad' => 'Bad: Water with suboptimal pH and ORP values may pose risks to health and water quality.',
        'disabled' => 'Non Available'
    ];

    /**
     * Synchronizes the provided value array with the default array.
     *
     * This function checks if the value array is lacking any keys present in the default array.
     * If it finds any, it adds them to the value array with the corresponding default value.
     * It also checks if the value array has any extra keys not present in the default array.
     * If it finds any, it removes them from the value array.
     *
     * @param array $default The default array to synchronize with.
     * @param array $value The array to be synchronized.
     * @param bool $doAdd Whether to add missing keys to the value array. Default is true.
     * @param bool $doRemove Whether to remove extra keys from the value array. Default is true.
     * @return array The synchronized array.
     */
    protected static function syncWithDefault(array $default, array $value, bool $doAdd = true, $doRemove = true): array
    {
        // check if lacking
        if ($doAdd)
            foreach ($default as $id => $name) {
                if (!isset($value[$id])) {
                    $value[$id] = $name;
                }
            }
        // check if more
        if ($doRemove)
            foreach ($value as $id => $name) {
                if (!in_array($id, array_keys($default))) {
                    unset($value[$id]);
                }
            }
        return $value;
    }

    // Profile Parameter e.g Internasional
    public static function getParameterProfile(): array
    {
        $parameterProfile = self::get('parameter_profile');
        $default = [
            'Internasional' => StatusController::$parametersThresholdInternational
        ];
        if (!$parameterProfile) {
            $parameterProfile = self::create([
                'key' => 'parameter_profile',
                'value' => $default,
            ]);
        }

        if (!is_array($parameterProfile->value)) {
            $parameterProfile->value = $default;
        }
        $value = $parameterProfile->value;

        // convert integer score to float based on green and yellow
        foreach ($value as $profile => $parameters) {
            foreach ($parameters as $i => $parameter) {
                if (isset($parameter['gs']) && isset($parameter['ge'])) {
                    if ($parameter['gs'] < $parameter['ge']) {
                        $value[$profile][$i]['score'] = self::$greenScoreMax;
                    } else if (isset($parameter['cs']) && isset($parameter['ce'])) {
                        if ($parameter['cs'] <= $parameter['ce']) {
                            $value[$profile][$i]['score'] = self::$yellowScoreMax;
                        }
                    }
                }
            }
        }
        return $value;
    }

    protected static $memCache = [];

    protected static function boot()
    {
        parent::boot();
        self::saved(function (AppSettings $model) {
            self::$memCache[$model->key] = $model;
        });
    }

    public static function get(string $key): \Illuminate\Database\Eloquent\Collection|array|AppSettings|null
    {
        if (isset(self::$memCache[$key])) {
            return self::$memCache[$key];
        }
        $result = self::where('key', $key)->first();
        self::$memCache[$key] = $result;
        return $result;
    }

    public static array $cache = [];

    public static function getS(string $key, mixed $default = null): mixed
    {
        if (app()->runningInConsole()) {
            return $default;
        }
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        $setting = static::where('key', $key)->first();
        if ($setting) {
            static::$cache[$key] = $setting->value;
            return $setting->value;
        }

        return $default;
    }

    // each pool has different profile
    public static function getPoolProfileParameter(): array
    {
        $poolProfileParameter = self::get('pool_profile_parameter');
        $default = [

        ];
        foreach (AppSettings::getDevices() as $id => $name) {
            $default[$id] = "Internasional";
        }
        if (!$poolProfileParameter) {
            $poolProfileParameter = self::create([
                'key' => 'pool_profile_parameter',
                'value' => $default,
            ]);
        }

        if (!is_array($poolProfileParameter->value)) {
            $poolProfileParameter->value = $default;
        }

        $value = $poolProfileParameter->value;
        return $value;
    }

    public static function getSensorsScoreMultiplier(): array
    {
        $poolProfileParameter = self::where('key', 'sensors_score_multiplier')->first();
        $default = [];
        $sensorsMultiplierDefault = [];
        foreach (self::$sensors as $sensor) {
            $sensorsMultiplierDefault[$sensor] = 1;
        }
        foreach (AppSettings::getDevices() as $id => $name) {
            $default[$id] = $sensorsMultiplierDefault;
        }
        if (!$poolProfileParameter) {
            $poolProfileParameter = self::create([
                'key' => 'sensors_score_multiplier',
                'value' => $default,
            ]);
        }
        $value = self::syncWithDefault($default, $poolProfileParameter->value);
        foreach ($value as $id => $sensorsMultiplier) {
            $value[$id] = self::syncWithDefault($sensorsMultiplierDefault, $sensorsMultiplier);
        }

        return $value;
    }
    public static function getAllowedSensors(): array
    {
        return session('allowed_sensors', []);
    }
}
