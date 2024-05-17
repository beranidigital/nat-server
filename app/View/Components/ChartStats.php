<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ChartStats extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $title, public array $labels, public array $values, public string $info = '')
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.chart-stats', [
            'title' => $this->title,
            'key' => 'charts-stats-' . md5($this->title),
            'labels' => $this->labels,
            'values' => $this->values,
            'info' => $this->info,
        ]);
    }
}
