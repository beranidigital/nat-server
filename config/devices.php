<?php

$conf = [
    'name' => env('DEVICES_NAME', \App\Models\AppSettings::$natwaveDevices),
    'sensors' => env('DEVICES_SENSORS', \App\Models\AppSettings::$sensors),
    'battery_sensors' => env('DEVICES_BATTERY_SENSORS', \App\Models\AppSettings::$batterySensors),
];

// convert string to array by splitting with space
if (is_string($conf['sensors'])) {
    $conf['sensors'] = explode(' ', $conf['sensors']);
}
if (is_string($conf['name'])) {
    $conf['name'] = explode(' ', $conf['name']);
}
if (is_string($conf['battery_sensors'])) {
    $conf['battery_sensors'] = explode(' ', $conf['battery_sensors']);
}

\App\Models\AppSettings::$natwaveDevices = $conf['name'];
\App\Models\AppSettings::$sensors = $conf['sensors'];
\App\Models\AppSettings::$batterySensors = $conf['battery_sensors'];
return $conf;
