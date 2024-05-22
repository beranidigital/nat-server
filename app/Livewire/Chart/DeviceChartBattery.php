<?php

namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;


class DeviceChartBattery extends ChartWidget
{
    public string $device;
    public array $filters = [];

    public function getDevicesName(): string
    {
        $battery = '';
        $stateLogs = StateLog::where('device', $this->device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['battery'])) {
                $battery = $stateLog['formatted_sensors']['battery']['label'];
            }
        }
        return $battery;
    }
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return $this->getDevicesName();
    }

    protected function getData(): array
    {
        $filters = ChartPoolDetail::extractFilter($this->filters);
        $startDate = $filters['startDate'] ?? now()->subWeek();
        $endDate = $filters['endDate'] ?? now();
        $frequency = $filters['frequency'] ?? 'Weekly';

        $frequencyEnum = IntervalFrequency::from($frequency);

        $stateLogs = StateLog::where('device', $this->device)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $data = $stateLogs->map(function ($stateLog) {
            return [
                'date' => $stateLog->created_at->format('d-m-Y'),
                'battery' => (float) ($stateLog->formatted_sensors['battery']['value'] ?? 0),
            ];
        });

        $groupedData = $data->groupBy(function ($item) use ($frequencyEnum) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                return Carbon::parse($item['date'])->format('Y-W');
            }
            return Carbon::parse($item['date'])->format('Y-m-d');
        });

        $labels = $groupedData->keys()->map(function ($date) use ($frequencyEnum) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-W', $date);
                if (count($split) === 2) {
                    $year = $split[0];
                    $week = $split[1];
                    return Carbon::now()->setISODate($year, $week)->startOfWeek()->format('d-m-Y');
                }
                return $date; // Return the original date if split is not as expected
            }
            return Carbon::parse($date)->format('d-m-Y');
        })->toArray();

        $batteryData = $groupedData->map(function ($items) {
            return $items->sum('battery');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Battery',
                    'data' => $batteryData,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getBattery(string $device): ?array
    {
        $battery = [];
        $stateLogs = StateLog::where('device', $device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['battery'])) {
                $battery['data'][] = (float) $stateLog['formatted_sensors']['battery']['value'];
            }
        }
        return $battery;
    }

    protected function getType(): string
    {
        return 'line';
    }
}

