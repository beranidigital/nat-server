<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use Faker\Factory;
use Faker\Generator;

class ScoreSimulationController extends Controller
{

    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public static function generateFakeData($parameterThreshold, Generator $faker)
    {
        $sensorsRange = self::extractSensorRangeFromProfile($parameterThreshold);
        $values = [];
        foreach ($sensorsRange as $sensor => $score) {
            $value = $faker->randomFloat(2, $score['min'], $score['max']);
            $values[$sensor] = $value;
        }
        return $values;
    }

    public function index()
    {
        $profileParameter = AppSettings::getParameterProfile();
        $charted = [];
        foreach ($profileParameter as $key => $value) {
            $charted[$key] = $this->simulate($value);
        }

        return view('score-simulation.index', [
            'profileParameter' => $profileParameter,
            'charted' => $charted,
        ]);
    }

    public function simulate($parameterThreshold, int $maxStep = 30)
    {
        $results = [];

        $sensorsRange = self::extractSensorRangeFromProfile($parameterThreshold, $maxStep);
        $offsetStep = intval($maxStep * 0.6);
        foreach ($sensorsRange as $sensor => $range) {
            $labels = [];
            $values = [];

            for ($i = $range['min'] - ($offsetStep * $range['step']); $i <= $range['max'] + ($offsetStep * $range['step']); $i += $range['step']) {
                // limit decimal to 2
                $labels[] = number_format($i, 2);
                $values[] = SensorDataController::calculateScoreWithParameter($sensor, $i, $parameterThreshold);
            }
            $results[$sensor] = [
                'labels' => $labels,
                'values' => $values,
            ];
        }
        return $results;

    }

    public static function extractSensorRangeFromProfile($profileParameter, int $totalStepping = 16)
    {
        $sensorsRange = [];
        foreach ($profileParameter as $threshold) {
            $sensor = $threshold['sensor'];
            $min = $threshold['min'];
            $max = $threshold['max'];

            $currentMin = $sensorsRange[$sensor]['min'] ?? $min;
            $currentMax = $sensorsRange[$sensor]['max'] ?? $max;

            $sensorsRange[$sensor] = [
                'min' => min($currentMin, $min),
                'max' => max($currentMax, $max),
                'step' => ($max - $min) / $totalStepping,
            ];
        }
        return $sensorsRange;
    }

}
