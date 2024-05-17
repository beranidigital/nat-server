<?php

namespace App\Http\Controllers\Pool;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;

class MainDashboardController extends Controller
{
    public function index()
    {
        $devices = AppSettings::getDevicesName()->value;


        $data = [
            'datas' => [],
        ];
        // Get latest sensor data for each device
        foreach ($devices as $device => $friendlyName) {
            $stateLog = StateLog::where('device', $device)
                ->orderBy('created_at', 'desc')
                ->first()?->toArray();
            if ($stateLog) {
                $data['datas'][$device] = $stateLog;
            }
        }


        // Devices and Label
        $data['devices'] = $devices;


        return view('dashboards.smart-home', $data);
    }
}
