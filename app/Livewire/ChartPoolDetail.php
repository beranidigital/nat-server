<?php

namespace App\Livewire;

use App\Enums\IntervalFrequency;
use App\Models\Pool\StateLog;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ChartPoolDetail extends Component
{
    protected static bool $shouldRegisterNavigation = false;
    public ?string $device = null;
    public ?string $deviceName = null;
    public array $filters = [];
    public StateLog $stateLog;
    public static function extractFilter(array $filters): array
    {

        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(7)->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::now()->endOfDay();

        if ($startDate) $startDate = \Illuminate\Support\Carbon::parse($startDate);
        if ($endDate) $endDate = \Illuminate\Support\Carbon::parse($endDate);
        if (!$startDate) $startDate = Carbon::parse(StateLog::min('created_at'));
        if (!$endDate) $endDate = Carbon::parse(StateLog::max('created_at'));

        $frequency = $filters['frequency'] ?? IntervalFrequency::Daily->name;


        return [
            'frequency' => $frequency,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function mount()
    {
        $this->device = request()->get('device');
        $this->stateLog = StateLog::where('device', $this->device)->firstOrFail();
        $this->filters = request()->all();
    }

    public function render()
    {
        return view('livewire.chart-pool-detail',[
            'device' => $this->device,
            'filters' => $this->filters,
        ]);
    }

}
