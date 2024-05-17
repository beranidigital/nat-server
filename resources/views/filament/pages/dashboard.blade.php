<x-filament-panels::page>
    @php
        $devices = App\Models\AppSettings1::getDevicesName()->value;
        $deviceName = '';
        foreach ($devices as $device => $friendlyName) {
            $stateLog = App\Models\Pool\StateLog::where('device', $device)->orderBy('created_at', 'desc')->first();
            $sensorData = [];

            if ($stateLog) {
                $sensorData = $stateLog->toArray();
            }
            $deviceName = $device;
        }
    @endphp
    <a href="{{ App\Filament\Pages\PoolDetail::getUrl(['device' => $deviceName]) }}">
        @livewire('dashboard')
    </a>
</x-filament-panels::page>
