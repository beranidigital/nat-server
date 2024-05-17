<?php

namespace App\Livewire;

use App\Http\Controllers\WaterpoolController;
use App\Models\AppSettings;
use App\Models\AppSettings1;
use App\Models\Pool\StateLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Colors\Color;

class DeviceStats extends BaseWidget
{

    public string $device;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // Limit to 1 day
        $stateLogs = StateLog::where('device', $this->device)->orderBy('created_at', 'desc')->limit(1 * 24 * 1)->get()->toArray();

        // Get allowed sensors
        $allowedSensors = WaterpoolController::getAllowedSensors();

        $sensorsData = []; // [sensor => [state1, state2, state3, ...]]
        if (empty($stateLogs)) {
            return [];
        }
        foreach ($stateLogs as $stateLog) {
            $formattedStatus = $stateLog['scores'];
            foreach ($stateLog['formatted_sensors'] as $sensor => $state) {
                if (!in_array($sensor, $allowedSensors)) {
                    continue;
                }
                if (!isset($sensorsData[$sensor])) {
                    $sensorsData[$sensor] = [];
                }
                $sensorsData[$sensor][] = $state['value'];
            }
        }

        $stats = [];
        $firstState = $stateLogs[0];
        $lastState = $stateLogs[count($stateLogs) - 1];
        $iconColor = '';

        foreach ($stateLogs[0]['formatted_sensors'] as $sensor => $state) {
            if (!in_array($sensor, $allowedSensors)) {
                continue;
            }
            if (!isset($sensorsData[$sensor])) {
                continue;
            }

            $diff = floatval($lastState['formatted_sensors'][$sensor]['value']) - floatval($firstState['formatted_sensors'][$sensor]['value']);

            $diffInPercent = 0;
            try {
                $diffInPercent = $diff / floatval($firstState['formatted_sensors'][$sensor]['value']) * 100;
            } catch (\DivisionByZeroError $e) {
                $diffInPercent = 0;
            }

           // Ambil skor dari state log
            $formattedStatus = $stateLogs[0]['scores'];
            $score = $formattedStatus[$sensor] ?? null;
            if ($score >= AppSettings1::$greenScoreMin && $score < AppSettings1::$greenScoreMax) {
                $iconColor =  Color::Emerald;
            } elseif ($score > AppSettings1::$yellowScoreMin && $score < AppSettings1::$yellowScoreMax) {
                $iconColor = Color::Yellow;
            } else{
                $iconColor = Color::Red;
            }

            $diffInPercent = round($diffInPercent, 2);
            $stats[] = Stat::make($state['label'], $state['value'] . ' ' . $state['unit'])
            ->description(($diff > 0 ? 'increase' : 'decrease') . ' by ' . abs($diffInPercent) . '%')
            ->descriptionIcon('heroicon-m-arrow-trending-' . ($diff > 0 ? 'up' : 'down'))
            ->chart($sensorsData[$sensor])
            ->color($iconColor);
        }

        return $stats;
    }
}
