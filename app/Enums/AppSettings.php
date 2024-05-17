<?php

namespace App\Enums;

use App\Models\UserType;
use Carbon\WeekDay;
use Filament\Support\Contracts\HasLabel;

enum AppSettings implements HasLabel
{
    case app_name;


    public function getLabel(): ?string
    {
        return __('app_settings.' . $this->name);
    }

    public function get()
    {
        $value = null;
        /// check if database exists (in console mode)
        if (!app()->runningInConsole()) {
            $value = \App\Models\AppSettings::getS($this->name);
        }
        if (!$value) {
            $value = $this->getDefault()[$this->name];
        }
        return $value;
    }

    public static function getDefault(): array
    {
        return [
            'app_name' => config('app.name'),
        ];
    }

    public function set(mixed $value): void
    {
        \App\Models\AppSettings::updateOrCreate([
            'key' => $this->name,
        ], [
            'value' => $value,
        ]);
    }
}
