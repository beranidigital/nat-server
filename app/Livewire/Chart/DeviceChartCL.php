<?php
namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\Pool\StateLog;
use Carbon\Carbon;
use Exception;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DeviceChartCL extends ChartWidget
{
    public function getDevicesName(): string
    {
        $cl = '';
        $stateLogs = StateLog::where('device', $this->device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['cl'])) {
                $cl = $stateLog['formatted_sensors']['cl']['label'];
            }
        }
        return $cl;
    }
    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return $this->getDevicesName();
    }
    public string $device;

    public array $filters = [];

    protected function getData(): array
    {
        $filters = ChartPoolDetail::extractFilter($this->filters);
        $startDate = $filters['startDate'] ?? now()->subDays(5);
        $endDate = $filters['endDate'] ?? now();
        $frequency = $filters['frequency'] ?? 'Daily';

        $frequencyEnum = IntervalFrequency::from($frequency);

        $cl = $this->getCl($this->device);
        $data = Trend::query(StateLog::query()->where('device', $this->device));
        if ($startDate && $endDate) {
            $data = $data->between($startDate, $endDate);
        }
        $data = $data->interval($frequencyEnum->toTrendInterval())->count();
        return [
            'datasets' => [
                [
                    'label' => 'Chlorine',
                    'data' => $cl['data'],
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

    public function getCl(string $device): ?array
    {
        $cl = [];
        $stateLogs = StateLog::where('device', $device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['cl'])) {
                $cl['data'][] = $stateLog['formatted_sensors']['cl']['value'];
            }
        }
        return $cl;
    }

    protected function getType(): string
    {
        return 'line';
    }

}
