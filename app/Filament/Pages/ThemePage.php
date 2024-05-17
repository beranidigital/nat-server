<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Hasnayeen\Themes\Filament\Pages\Themes as ThemesPage;

class ThemePage extends ThemesPage
{
    use HasPageShield;

    protected static ?string $navigationGroup = 'settings';
    protected static ?string $navigationIcon = null;
    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    public static function getNavigationLabel(): string
    {
        return __('Themes');
    }

    public function mount(): void
    {
        // Prevent 403
    }
}
