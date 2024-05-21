<div>
    @livewire('Chart.device-chart-battery', ['device' => $device, 'filters' => $this->filters])
    <br>
    @livewire('Chart.device-chart-cl', ['device' => $device,'filters' => $this->filters])
    <br>
    @livewire('Chart.device-chart-conductivity', ['device' => $device,'filters' => $this->filters])
    <br>
    @livewire('Chart.device-chart-orp', ['device' => $device, 'filters' => $this->filters])
    <br>
    @livewire('Chart.device-chart-ph', ['device' => $device, 'filters' => $this->filters])
    <br>
    @livewire('Chart.device-chart-temp', ['device' => $device, 'filters' => $this->filters])
    <br>
    @livewire('Chart.device-chart-tds', ['device' => $device, 'filters' => $this->filters])
</div>
