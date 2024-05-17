<?php

namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\Pool\StateLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;


class DeviceChartBattery extends ChartWidget
{
    protected static ?string $heading = 'Battery';

    public string $device;
    public array $filters = [];

    protected function getData(): array
    {
        $filters = ChartPoolDetail::extractFilter($this->filters);
        $startDate = $filters['startDate'] ?? now()->subMonth();
        $endDate = $filters['endDate'] ?? now();
        $frequency = $filters['frequency'] ?? 'Weekly';

        $frequencyEnum = IntervalFrequency::from($frequency);

        $battery = $this->getBattery($this->device);
        $data = Trend::query(StateLog::query()->where('device', $this->device));
        if ($startDate && $endDate) {
            $data = $data->between($startDate, $endDate);
        }
        $data = $data->interval($frequencyEnum->toTrendInterval())->count();
        return [
            'datasets' => [
                [
                    'label' => 'Chlorine',
                    'data' => $battery['data'],
                ],
            ],
              'labels' => $data->map(function ($value) use ($frequencyEnum) {
                if ($frequencyEnum === IntervalFrequency::Weekly) {
                    $split = explode('-', $value->date);
                    $value->date = $split[0] . '-W' . $split[1];
                }
                $date = $value->date instanceof Carbon ? $value->date->format('Y-m-d H:i:s') : $value->date;

                return Carbon::parse($date)->format('M d H:i');
            })->toArray(),
        ];
    }

    public function getBattery(string $device): ?array
    {
        $battery = [];
        $stateLogs = StateLog::where('device', $device)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['battery'])) {
                $battery['data'][] = $stateLog['formatted_sensors']['battery']['value'];
            }
        }
        return $battery;
    }

    // protected function getData(): array
    // {
    //     $stateLogs = StateLog::where('device', $this->device)
    //         ->orderBy('created_at', 'asc') // Order by ascending dates
    //         ->get()
    //         ->toArray();

    //     $sensorData = [];

    //     foreach ($stateLogs as $stateLog) {
    //         $createdAt = Carbon::parse($stateLog['created_at'])->format('Y-m-d H:i');
    //         foreach ($stateLog['formatted_sensors'] as $sensor => $state) {
    //             if (!isset($sensorData[$sensor])) {
    //                 $sensorData[$sensor] = [
    //                     'labels' => [],
    //                     'data' => [],
    //                 ];
    //             }
    //             $sensorData[$sensor]['labels'][] = $createdAt;
    //             $sensorData[$sensor]['data'][] = $state['value'];
    //         }
    //     }

    //     $datasets = [];
    //     foreach ($sensorData as $sensor => $data) {
    //         $datasets[] = [
    //             'label' => $sensor,
    //             'data' => $data['data'],
    //         ];
    //     }

    //     // Prepare chart data
    //     $chartData = [
    //         'datasets' => $datasets,
    //         'labels' => $sensorData ? $sensorData[array_key_first($sensorData)]['labels'] : [], // Use labels from the first sensor
    //     ];

    //     return $chartData;
    // }

    protected function getType(): string
    {
        return 'line';
    }
}

