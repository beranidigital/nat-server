<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
