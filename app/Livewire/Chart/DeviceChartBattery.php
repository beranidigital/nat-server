<?php
namespace App\Livewire\Chart;
use App\Enums\IntervalFrequency;
use App\Livewire\ChartPoolDetail;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;

class DeviceChartBattery extends ChartWidget
{
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

    public string $device;



    public array $filters = [];



    protected function getData(): array

    {

        $filters = ChartPoolDetail::extractFilter($this->filters);

        $startDate = $filters['startDate'] ;

        $endDate = $filters['endDate'];

        $frequency = $filters['frequency'];

        $frequencyEnum = IntervalFrequency::from($frequency);


        $battery = $this->getBattery($this->device);
        $data = Trend::query(StateLog::query()->when($battery, fn ($query) => $query->where('device',$this->device)));

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
        $dataBattery = $data->toArray();
        $dates = [];
        foreach ($dataBattery as $trendValue) {
            if ($frequencyEnum === IntervalFrequency::Weekly) {
                $split = explode('-', $trendValue->date);
                $trendValue->date = $split[0] . '-W' . $split[1];
                $dates[] = $trendValue->date;
            } else {
                $dates[] = \Illuminate\Support\Carbon::parse($trendValue->date)->format('d-m-Y');
            }
        }

        $validDates = array_intersect($dates, $battery['date']);
        $filteredData = [];
        $filteredDates = [];

        foreach ($dates as $date) {
            if (in_array($date, $battery['date'])) {
                $indices = array_keys($battery['date'], $date);
                foreach ($indices as $index) {
                    if($battery['data'][$index] == 'unknown' || $battery['data'][$index] == 'unvailable'){
                        $filteredData[] = 0;
                        $filteredDates[] = $date;
                    } else {
                        $filteredData[] = $battery['data'][$index];
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


    public function getBattery(string $device): ?array

    {

        $battery = [];

        $stateLogs = StateLog::where('device', $device)

            ->limit(1 * 24 * 1)

            ->orderBy('created_at', 'asc')

            ->get()

            ->toArray();



        foreach ($stateLogs as $stateLog) {
            $battery['date'][] = Carbon::parse($stateLog['created_at'])->format('d-m-Y');
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
