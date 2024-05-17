<?php
namespace App\Filament\Pages;

use App\Models\Pool\StateLog;
use Filament\Forms\Components\Section;
use Filament\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class AppSettings1 extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationGroup = 'settings';

    protected static string $view = 'filament.pages.app-settings1';

    public function getTitle(): string|Htmlable
    {
        return self::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('Application Settings');
    }

}
