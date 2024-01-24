<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WaterpoolController extends Controller
{


    public static function getStates(string $deviceName = null, int $limit = 15): array
    {
        $datas = SensorDataController::getStats($deviceName, $limit);
        $sensors = [];
        for ($i = 0; $i < $limit; $i++) {
            $state = [];
            $averageTimestamp = 0;

            foreach ($datas as $sensor => $data) {
                $state[$sensor] = $data['data'][$i];
                $averageTimestamp += strtotime($data['timestamp'][$i]);
            }
            $averageTimestamp /= count($datas);
            $state['timestamp'] = $averageTimestamp;
            $sensors[] = $state;
        }
        return $sensors;
    }

    public static function formatStates(array $states): array
    {
        $formattedStates = [];
        foreach ($states as $state) {
            $formattedState = [];
            foreach ($state as $sensor => $value) {
                $formattedState[$sensor] = self::formatSensor($sensor, $value);
            }
            $formattedStates[] = $formattedState;
        }
        return $formattedStates;
    }


    public static function formatSensor(string $sensor, $value)
    {
        if ($sensor == 'timestamp') return date('Y-m-d H:i:s', $value);

        $sensor_name = explode('_', $sensor)[1];
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
                return StatusController::formatTemperature(floatval($value));
            default:
                //throw new \Exception("Unknown sensor: {$sensor_name}");
                Log::warning("Unknown sensor: {$sensor_name}");
                return [
                    'value' => $value,
                    'unit' => '',
                    'label' => __('translation.' . $sensor_name),
                ];
        }
    }

    public function index()
    {

        $status = $this->getStates();
        return view('waterpool/5-table-status', compact('status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
