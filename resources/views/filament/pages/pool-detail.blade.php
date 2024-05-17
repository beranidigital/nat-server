<x-filament-panels::page>
    @livewire('device-stats', ['device' => $device])
    @livewire('filter-timeline', ['device' => $device])
    <x-filament::section>
        <x-slot name="heading">
            {{ __($this->deviceName . ' Analytic') }}
        </x-slot>
        @livewire('chart-pool-detail', ['device' => $device])
    </x-filament::section>
    @livewire('device-table', ['device' => $device])
</x-filament-panels::page>
