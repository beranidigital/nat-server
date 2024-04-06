<?php

namespace App\Http\Controllers\Pool;

use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Illuminate\Http\Request;

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


    public function toggleDevice(Request $request)
    {
        $deviceId = $request->input('deviceId');
        $status = $request->input('status');

        // Cek apakah DeviceId sudah ada di database
        $appSetting = AppSettings::where('key', $deviceId)->first();

        if ($appSetting) {
            // Jika DeviceId sudah ada, update nilai statusnya
            $appSetting->update(['value' => $status]);
        } else {
            // Jika DeviceId belum ada, tambahkan ke database
            AppSettings::create(['key' => $deviceId, 'value' => $status]);
        }

        // Berikan respons sesuai kebutuhan, misalnya dengan JSON
        return response()->json(['message' => 'Device status updated successfully']);
        
    }
}
