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

class DeviceChartPH extends ChartWidget
{
    public function getDevicesName(): string
    {
        $ph = '';
        $stateLogs = StateLog::where('device', $this->device)
        ->limit(1 * 24 * 1)
        ->orderBy('created_at', 'asc')
        ->get()
        ->toArray();

        foreach ($stateLogs as $stateLog) {
            if (isset($stateLog['formatted_sensors']['ph'])) {
                $ph = $stateLog['formatted_sensors']['ph']['label'];
            }
        }
        return $ph;
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


        $ph = $this->getPH($this->device);
        $data = Trend::query(StateLog::query()->when($ph, fn ($query) => $query->where('device',$this->device)));

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
        $dataPH = $data->toArray();
        $dates = [];
        foreach ($dataPH as $trendValue) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-', $trendValue->date);
                $trendValue->date = $split[0] . '-W' . $split[1];
                $dates[] = $trendValue->date;
            } else {
                $dates[] = \Illuminate\Support\Carbon::parse($trendValue->date)->format('d-m-Y');
            }
        }

        $validDates = array_intersect($dates, $ph['date']);
        $filteredData = [];
        $filteredDates = [];

        foreach ($dates as $date) {
            if (in_array($date, $ph['date'])) {
                $indices = array_keys($ph['date'], $date);
                foreach ($indices as $index) {
                    if($ph['data'][$index] == 'unknown' || $ph['data'][$index] == 'unvailable'){
                        $filteredData[] = 0;
                        $filteredDates[] = $date;
                    } else {
                        $filteredData[] = $ph['data'][$index];
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
            'labels' => $filteredDates
        ];
    }


    public function getPH(string $device): ?array

    {

        $ph = [];
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
                $ph['date'][] = $startOfDay->format('d-m-Y');
                if (isset($morningLog['formatted_sensors']['ph'])) {
                    $ph['data'][] = $morningLog['formatted_sensors']['ph']['value'];
                } else {
                    $ph['data'][] = 0;
                }
            }

            if ($eveningLog) {
                $ph['date'][] = $endOfDay->format('d-m-Y');
                if (isset($eveningLog['formatted_sensors']['ph'])) {
                    $ph['data'][] = $eveningLog['formatted_sensors']['ph']['value'];
                } else {
                    $ph['data'][] = 0;
                }
            }
        }
        return $ph;

    }
    protected function getType(): string
    {
        return 'line';
    }
}
