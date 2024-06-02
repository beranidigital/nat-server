<?php

namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\Pool\StateLog;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Livewire\Component;

class DeviceChartConductivity extends ChartWidget
{
    public function getDevicesName(): string
    {
        $ec = '';
        $stateLogs = StateLog::where('device', $this->device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['ec'])) {
                $ec = $stateLog['formatted_sensors']['ec']['label'];
            }
        }
        return $ec;
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

        $startDate = $filters['startDate'] ;

        $endDate = $filters['endDate'];

        $frequency = $filters['frequency'];

        $frequencyEnum = IntervalFrequency::from($frequency);


        $ec = $this->getEc($this->device);
        $data = Trend::query(StateLog::query()->when($ec, fn ($query) => $query->where('device',$this->device)));

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
        $dataEc = $data->toArray();
        $dates = [];
        foreach ($dataEc as $trendValue) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-', $trendValue->date);
                $trendValue->date = $split[0] . '-W' . $split[1];
                $dates[] = $trendValue->date;
            } else {
                $dates[] = \Illuminate\Support\Carbon::parse($trendValue->date)->format('d-m-Y');
            }
        }

        $validDates = array_intersect($dates, $ec['date']);
        $filteredData = [];
        $filteredDates = [];

        foreach ($dates as $date) {
            if (in_array($date, $ec['date'])) {
                $indices = array_keys($ec['date'], $date);
                foreach ($indices as $index) {
                    if($ec['data'][$index] == 'unknown' || $ec['data'][$index] == 'unavailable'){
                        $filteredData[] = 0;
                        $filteredDates[] = $date;
                    } else {
                        $filteredData[] = $ec['data'][$index];
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
            'labels' => $filteredDates,
        ];
    }


    public function getEc(string $device): ?array
    {
        $ec = [];
        $now = Carbon::now();

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
                $ec['date'][] = $startOfDay->format('d-m-Y');
                if (isset($morningLog['formatted_sensors']['ec'])) {
                    $ec['data'][] = $morningLog['formatted_sensors']['ec']['value'];
                } else {
                    $ec['data'][] = 0;
                }
            }

            if ($eveningLog) {
                $ec['date'][] = $endOfDay->format('d-m-Y');
                if (isset($eveningLog['formatted_sensors']['ec'])) {
                    $ec['data'][] = $eveningLog['formatted_sensors']['ec']['value'];
                } else {
                    $ec['data'][] = 0;
                }
            }
        }

        return $ec;
    }


    protected function getType(): string
    {
        return 'line';
    }
}
