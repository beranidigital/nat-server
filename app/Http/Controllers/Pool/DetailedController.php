<?php

namespace App\Http\Controllers\Pool;

use App\Exports\SensorDataExport;
use App\Http\Controllers\Controller;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DetailedController extends Controller
{
    public function index()
    {
        $data = [];
        $deviceName = request()->get('device', key(AppSettings::getDevices()));


        $interval = 60 * 60; // 60 minutes
        if (request()->has('interval')) {
            try {
                $interval = request()->get('interval');
                $interval = intval($interval);
                $interval = $interval > 0 ? $interval : 60 * 60;

            } catch (NotFoundExceptionInterface $e) {
            } catch (ContainerExceptionInterface $e) {
            }
        }


        $data['deviceName'] = $deviceName;

        // get the latest state
        $data['latestStates'] =
            Cache::remember('latestStates', config('cache.time') * 0.4, function () use ($deviceName) {
                return StateLog::where('device', $deviceName)
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->get()
                    ->toArray();
            });
        $data['latestState'] = $data['latestStates'][0] ?? [];

        // date filter
        $max_date = StateLog::max('created_at');
        $min_date = StateLog::min('created_at');
        $data['date_filter'] = [
            'max' => strtotime($max_date),
            'min' => strtotime($min_date),
        ];

        // Make statistic from the latest state
        $stats = [
            // 'ph' => [
            //  data => [4, 5, 6, 7, 8, 9, 10],
            //  timestamp => [time1, time2, time3, time4, time5, time6, time7],
            // ],
        ];
        DB::statement("SET sql_mode = ''");

        //SELECT *, (state_logs.created_at - INTERVAL MOD(UNIX_TIMESTAMP(`created_at`),2700)-1 SECOND ) AS piss  FROM state_logs GROUP BY piss;
        $queryHash = md5($deviceName . $interval . '15' . '2');
        $statStates =
            Cache::remember('statStates.' . $queryHash, config('cache.time'), function () use ($deviceName, $interval) {
                return StateLog::withoutAppends()
                    ->selectRaw('*, (state_logs.created_at - INTERVAL MOD(UNIX_TIMESTAMP(`created_at`), ' . $interval . ')-1 SECOND ) AS piss')
                    ->where('device', $deviceName)
                    ->orderBy('created_at', 'desc')
                    ->groupBy('piss')
                    ->limit(15)
                    ->get()
                    ->toArray();
            });

        foreach (array_reverse($statStates) as $state) {
            foreach ($state['sensors'] as $sensor => $value) {
                if (!isset($stats[$sensor])) {
                    $stats[$sensor] = [
                        'data' => [],
                        'timestamp' => [],
                    ];
                }
                $stats[$sensor]['data'][] = $value;
                $stats[$sensor]['timestamp'][] = date('M d H:i', strtotime($state['created_at']));
            }
        }
        $data['stats'] = $stats;

        return view('dashboards/detailed-dashboard', $data);
    }

    public function export()
    {
        $isPdf = request()->get('isPdf', false);


        $deviceName = request()->get('deviceName', key(AppSettings::getDevices()));
        // check if device name is valid
        if (!in_array($deviceName, array_keys(AppSettings::getDevices()))) {
            abort(404);
        }

        if ($isPdf) {
            return Excel::download(new SensorDataExport($deviceName), "sensor_data_{$deviceName}.pdf", \Maatwebsite\Excel\Excel::TCPDF);
        }
        return Excel::download(new SensorDataExport($deviceName), "sensor_data_{$deviceName}.xlsx");
    }

}
