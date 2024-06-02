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

class DeviceChartORP extends ChartWidget
{
    public function getDevicesName(): string
    {
        $orp = '';
        $stateLogs = StateLog::where('device', $this->device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['orp'])) {
                $orp = $stateLog['formatted_sensors']['orp']['label'];
            }
        }
        return $orp;
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


        $orp = $this->getOrp($this->device);
        $data = Trend::query(StateLog::query()->when($orp, fn ($query) => $query->where('device',$this->device)));

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
        $dataOrp = $data->toArray();
        $dates = [];
        foreach ($dataOrp as $trendValue) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-', $trendValue->date);
                $trendValue->date = $split[0] . '-W' . $split[1];
                $dates[] = $trendValue->date;
            } else {
                $dates[] = \Illuminate\Support\Carbon::parse($trendValue->date)->format('d-m-Y');
            }
        }

        $validDates = array_intersect($dates, $orp['date']);
        $filteredData = [];
        $filteredDates = [];

        foreach ($dates as $date) {
            if (in_array($date, $orp['date'])) {
                $indices = array_keys($orp['date'], $date);
                foreach ($indices as $index) {
                    if($orp['data'][$index] == 'unknown' || $orp['data'][$index] == 'unavailable'){
                        $filteredData[] = 0;
                        $filteredDates[] = $date;
                    } else {
                        $filteredData[] = $orp['data'][$index];
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


    public function getOrp(string $device): ?array

    {

        $orp = [];

        $stateLogs = StateLog::where('device', $device)

            ->limit(1 * 24 * 1)

            ->orderBy('created_at', 'asc')

            ->get()

            ->toArray();



        foreach ($stateLogs as $stateLog) {
            $orp['date'][] = Carbon::parse($stateLog['created_at'])->format('d-m-Y');
            if (isset($stateLog['formatted_sensors']['orp'])) {
                $orp['data'][] = $stateLog['formatted_sensors']['orp']['value'];
            }

        }
        $now =  \Illuminate\Support\Carbon::now();

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
                $orp['date'][] = $startOfDay->format('d-m-Y');
                if (isset($morningLog['formatted_sensors']['orp'])) {
                    $orp['data'][] = $morningLog['formatted_sensors']['orp']['value'];
                } else {
                    $orp['data'][] = 0;
                }
            }

            if ($eveningLog) {
                $orp['date'][] = $endOfDay->format('d-m-Y');
                if (isset($eveningLog['formatted_sensors']['orp'])) {
                    $orp['data'][] = $eveningLog['formatted_sensors']['orp']['value'];
                } else {
                    $orp['data'][] = 0;
                }
            }
        }
        return $orp;

    }


    protected function getType(): string
    {
        return 'line';
    }
}
