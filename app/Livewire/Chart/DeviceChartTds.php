<?php

namespace App\Livewire\Chart;

use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\Pool\StateLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Livewire\Component;

class DeviceChartTds extends ChartWidget
{
    public function getDevicesName(): string
    {
        $tds = '';
        $stateLogs = StateLog::where('device', $this->device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['tds'])) {
                $tds = $stateLog['formatted_sensors']['tds']['label'];
            }
        }
        return $tds;
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


        $tds = $this->getTds($this->device);
        $data = Trend::query(StateLog::query()->when($tds, fn ($query) => $query->where('device',$this->device)));

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
        $dataTds = $data->toArray();
        $dates = [];
        foreach ($dataTds as $trendValue) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-', $trendValue->date);
                $trendValue->date = $split[0] . '-W' . $split[1];
                $dates[] = $trendValue->date;
            } else {
                $dates[] = \Illuminate\Support\Carbon::parse($trendValue->date)->format('d-m-Y');
            }
        }

        $validDates = array_intersect($dates, $tds['date']);
        $filteredData = [];
        $filteredDates = [];

        foreach ($dates as $date) {
            if (in_array($date, $tds['date'])) {
                $indices = array_keys($tds['date'], $date);
                foreach ($indices as $index) {
                    if($tds['data'][$index] == 'unknown' || $tds['data'][$index] == 'unvailable'){
                        $filteredData[] = 0;
                        $filteredDates[] = $date;
                    } else {
                        $filteredData[] = $tds['data'][$index];
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


    public function getTds(string $device): ?array

    {

        $tds = [];

        $stateLogs = StateLog::where('device', $device)

            ->limit(1 * 24 * 1)

            ->orderBy('created_at', 'asc')

            ->get()

            ->toArray();



        foreach ($stateLogs as $stateLog) {
            $tds['date'][] = Carbon::parse($stateLog['created_at'])->format('d-m-Y');
            if (isset($stateLog['formatted_sensors']['tds'])) {
                $tds['data'][] = $stateLog['formatted_sensors']['tds']['value'];
            }

        }

        return $tds;

    }


    protected function getType(): string
    {
        return 'line';
    }
}
