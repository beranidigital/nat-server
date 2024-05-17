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
    use InteractsWithForms, HasPageShield;

    protected static ?string $navigationGroup = 'settings';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.app-settings';

    public array $data;

    public function __construct()
    {
        $this->authorize('viewAny', [\App\Models\AppSettings::class]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('viewAny', [\App\Models\AppSettings::class]);
    }

    public function getTitle(): string|Htmlable
    {
        return self::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('General Settings');
    }

    public function mount(): void
    {
        $this->data = [];
        $this->data['app_name'] = config('app.name');

        $appSettings = \App\Enums\AppSettings::cases();

        foreach ($appSettings as $appSetting) {
            $this->data[$appSetting->name] = $appSetting->get();
        }


        $this->form->fill($this->data);
    }

    public function submit(): void
    {
        // Check permissions
        $this->authorize('update', [\App\Models\AppSettings::class]);
        $state = $this->form->getState();

        if ($this->data['app_name'] != config('app.name')) {
            self::setAppName($this->data['app_name']);
        }


        foreach ($state as $key => $value) {

            \App\Models\AppSettings::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }


        Notification::make()
            ->title(__('Updated Item'))
            ->success()
            ->send();
    }

    public static function setAppName(string $appName)
    {
        // hacking env
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        // sanitizing, only allow alphanumeric, underscore, and space
        $appName = preg_replace("/[^a-zA-Z0-9_ ]+/", "", $appName);
        // warp in double quote
        $appName = '"' . $appName . '"';
        $str = preg_replace("/APP_NAME=(.*)/", "APP_NAME=$appName", $str); //hopium
        file_put_contents($envFile, $str);
    }

    public function form(Form $form): Form
    {
        // Add settings here
        $schema = [
            Grid::make()->columns(2)->schema([
                TextInput::make('app_name')
                    ->label(__('App Name'))
                    ->required()
                    ->autofocus()
                    ->placeholder(__('app_name')),
            ]),
        ];


        return $form
            ->schema($schema)
            ->statePath('data');
    }
}
