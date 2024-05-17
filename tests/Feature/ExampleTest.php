<?php

namespace Tests\Feature;

use App\Http\Controllers\StatusController;
use App\Models\AppSettings;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_scoring()
    {
        $state = [];
        $sensorsRange = [];
        foreach (StatusController::$parametersThresholdInternational as $threshold) {
            $sensor = $threshold['sensor'];
            $min = $threshold['min'];
            $max = $threshold['max'];

            $currentMin = isset($sensorsRange[$sensor]['min']) ? $sensorsRange[$sensor]['min'] : $min;
            $currentMax = isset($sensorsRange[$sensor]['max']) ? $sensorsRange[$sensor]['max'] : $max;

            $sensorsRange[$sensor] = [
                'min' => min($currentMin, $min),
                'max' => max($currentMax, $max),
            ];
        }
        $scores = [];
        $faker = \Faker\Factory::create();
        foreach ($sensorsRange as $sensor => $score) {
            $value = $faker->randomFloat(2, $score['min'], $score['max']);
            $scores[$sensor] = StatusController::calculateScoreFor($sensor, $value, AppSettings::$natwaveDevices[0]);
        }
        $this->assertIsArray($scores);
        $finalScore = StatusController::calculateFinalScore($scores);
        $this->assertIsFloat($finalScore);
    }
}
