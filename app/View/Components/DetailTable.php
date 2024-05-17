<?php

namespace App\View\Components;

use App\Models\Pool\StateLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class DetailTable extends Component
{
    public $deviceName;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $deviceName)
    {
        $this->deviceName = $deviceName;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {

        $status =
            Cache::remember('status.' . $this->deviceName, config('cache.time'), function () {
                return StateLog::where('device', $this->deviceName)->orderBy('created_at', 'desc')
                    ->limit(15)
                    ->get()
                    ->toArray();
            });

        return view('components.detail-table', compact('status'));
    }
}
