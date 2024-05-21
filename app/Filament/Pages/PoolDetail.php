<?php

namespace App\Filament\Pages;

use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class PoolDetail extends Page
{
    protected static ?string $navigationGroup = 'details';

    protected static string $view = 'filament.pages.pool-detail';
    protected static bool $shouldRegisterNavigation = false;
    public ?string $device = null;

    public ?string $deviceName = null;
    public StateLog $stateLog;
    public function mount()
    {
        $this->device = request()->get('device');
        $this->stateLog = StateLog::where('device', $this->device)->firstOrFail();
        $this->deviceName = $this->getTitle();
    }
    public function getDevicesName(): array
    {
        $devices = AppSettings::getDevicesName()->value;
        return $devices;
    }

    public function getTitle(): string|Htmlable
    {
        $devices = $this->getDevicesName();
        return $devices[$this->device];
    }
}
