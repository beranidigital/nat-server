<?php

namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\Pool\StateLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Livewire\Component;

class DeviceChartConductivity extends ChartWidget
{
    protected static ?string $heading = 'Conductivity';

    public string $device;
    public array $filters = [];

    protected function getData(): array
    {
        $filters = ChartPoolDetail::extractFilter($this->filters);
        $startDate = $filters['startDate'] ?? now()->subDays(7);
        $endDate = $filters['endDate'] ?? now();
        $frequency = $filters['frequency'] ?? 'Weekly';

        $frequencyEnum = IntervalFrequency::from($frequency);

        $ec = $this->getEc($this->device);
        $data = Trend::query(StateLog::query()->where('device', $this->device));
        if ($startDate && $endDate) {
            $data = $data->between($startDate, $endDate);
        }
        $data = $data->interval($frequencyEnum->toTrendInterval())->count();
        return [
            'datasets' => [
                [
                    'label' => 'Conductivity',
                    'data' => $ec['data'],
                ],
            ],
              'labels' => $data->map(function ($value) use ($frequencyEnum) {
                if ($frequencyEnum === IntervalFrequency::Weekly) {
                    $split = explode('-', $value->date);
                    $value->date = $split[0] . '-W' . $split[1];
                }
                return Carbon::parse($value->date)->format('d-m-Y');
            })->toArray(),
        ];
    }

    public function getEc(string $device): ?array
    {
        $ec = [];
        $stateLogs = StateLog::where('device', $device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['ec'])) {
                $ec['data'][] = $stateLog['formatted_sensors']['ec']['value'];
            }
        }
        return $ec;
    }


    protected function getType(): string
    {
        return 'line';
    }
}
