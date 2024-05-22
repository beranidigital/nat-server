<?php

namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;


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
      

        $battery = $this->getBattery($this->device);
        $data = Trend::query(StateLog::query()->where('device', $battery));
        if ($startDate && $endDate) {
            $data = $data->between($startDate, $endDate);
        }
        $data = $data->interval($frequencyEnum->toTrendInterval())->count();
        return [
            'datasets' => [
                [
                    'label' => 'Battery',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                ],
            ],
              'labels' => $data->map(function ($value) use ($frequencyEnum) {
                if ($frequencyEnum === IntervalFrequency::Weekly) {
                    $split = explode('-', $value->date);
                    $value->date = $split[0] . '-W' . $split[1];
                    return Carbon::parse($value->date)->format('d-m-Y');
                }
                return Carbon::parse($value->date)->format('d-m-Y');
            })->toArray(),
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
                $battery['data'][] = $stateLog['formatted_sensors']['battery']['value'];
            }
        }
        return $battery;
    }

    protected function getType(): string
    {
        return 'line';
    }
}

