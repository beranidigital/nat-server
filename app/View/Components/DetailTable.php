<?php

namespace App\View\Components;

use App\Http\Controllers\WaterpoolController;
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
        $status = WaterpoolController::getStates($this->deviceName);
        return view('components.detail-table', compact('status'));
    }
}