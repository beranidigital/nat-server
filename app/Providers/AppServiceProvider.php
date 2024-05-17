<?php

namespace App\Providers;

use App\Models\AppSettings;
use Carbon\Carbon;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

// use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        FilamentAsset::register([
            Css::make('app', Vite::asset('resources/css/app.css')),
        ]);
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
        if (app()->runningInConsole()) return;
        $devices_name = AppSettings::getDevicesName();
        foreach ($devices_name->value as $id => $name) {

            app('translator')->addLines([
                'devices_name.' . $id => $name,
            ], 'id');
        }


        $translation = AppSettings::getTranslation();
        foreach ($translation->value as $key => $value) {
            app('translator')->addLines([
                'translation.' . $key => $value,
            ], 'id');
        }
    }
}
