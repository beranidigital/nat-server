<?php
namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\Pool\StateLog;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;


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
        $startDate = $filters['startDate'] ?? now()->subDay();
        $endDate = $filters['endDate'] ?? now();
        $frequency = $filters['frequency'] ?? 'Daily';

        $frequencyEnum = IntervalFrequency::from($frequency);

        $cl = $this->getCl($this->device);
        $data = Trend::query(StateLog::query()->when($cl, fn ($query) => $query->where('device',$this->device)));

        if($startDate && $endDate)
        {
            $data->between($startDate,$endDate);
        }
        switch ($frequencyEnum) {
            case IntervalFrequency::Daily:
                $data->perDay();
                break;
            case IntervalFrequency::Weekly:
                $data->perWeek();
                break;
            default:
                $data->perMonth();
                break;
        }
        $data = $data->count();
        $dataCl = $data->toArray();
        $dates = [];
        foreach ($dataCl as $trendValue) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-', $trendValue->date);
                $trendValue->date = $split[0] . '-W' . $split[1];
                $dates[] = $trendValue->date;
            } else {
                $dates[] = \Illuminate\Support\Carbon::parse($trendValue->date)->format('d-m-Y');
            }
        }

        $validDates = array_intersect($dates, $cl['date']);
        $filteredData = [];
        $filteredDates = [];

        foreach ($dates as $date) {
                if (in_array($date, $cl['date'])) {
                    $indices = array_keys($cl['date'], $date);
                    foreach ($indices as $index) {
                        if($cl['data'][$index] == 'unknown' || $cl['data'][$index] == 'unvailable'){
                            $filteredData[] = 0;
                            $filteredDates[] = $date;
                        } else {
                            $filteredData[] = $cl['data'][$index];
                            $filteredDates[] = $date;
                        }
                    }
                } else {
                    $filteredData[] = 0;
                    $filteredDates[] = $date;
                }
        }
        return [
            'datasets' => [
                [
                    'label' => $this->getDevicesName(),
                    'data' =>  $filteredData,
                ],
            ],
            'labels' => $data->map(function ($value) use ($frequencyEnum,$dates) {
                if ($frequencyEnum === IntervalFrequency::Weekly) {
                    $split = explode('-', $value->date);
                    $value->date = $split[0] . '-W' . $split[1];
                    return $value->date;
                }
                return Carbon::parse($value->date)->format('d-m-Y');
            })->toArray(),
        ];
    }

    public function getCl(string $device): ?array
    {
        $cl = [];
        $now =  Carbon::now();

        for ($i = 0; $i < 7; $i++) {
            $startOfDay = $now->copy()->subDays($i)->startOfDay(); // 00:00:00
            $midDay = $startOfDay->copy()->addHours(12); // 12:00:00
            $endOfDay = $startOfDay->copy()->endOfDay(); // 23:59:59

            $morningLog = StateLog::where('device', $device)
                ->whereBetween('created_at', [$startOfDay, $midDay])
                ->orderBy('created_at', 'asc')
                ->first();

            $eveningLog = StateLog::where('device', $device)
                ->whereBetween('created_at', [$midDay, $endOfDay])
                ->orderBy('created_at', 'asc')
                ->first();

            if ($morningLog) {
                $cl['date'][] = $startOfDay->format('d-m-Y');
                if (isset($morningLog['formatted_sensors']['cl'])) {
                    $cl['data'][] = $morningLog['formatted_sensors']['cl']['value'];
                } else {
                    $cl['data'][] = 0;
                }
            }

            if ($eveningLog) {
                $cl['date'][] = $endOfDay->format('d-m-Y');
                if (isset($eveningLog['formatted_sensors']['cl'])) {
                    $cl['data'][] = $eveningLog['formatted_sensors']['cl']['value'];
                } else {
                    $cl['data'][] = 0;
                }
            }
        }
        return $cl;
    }

    protected function getType(): string
    {
        return 'line';
    }

}
