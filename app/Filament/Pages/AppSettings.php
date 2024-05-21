<?php

namespace App\Filament\Pages;

use App\Enums\AppSettingsImage;
use App\Enums\AppSettingsModule;
use App\Enums\ProjectModule;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\WeekDay;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class AppSettings extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationGroup = 'settings';

    protected static string $view = 'filament.pages.app-settings';

    public function getTitle(): string|Htmlable
    {
        return self::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('Application Settings');
    }

}

