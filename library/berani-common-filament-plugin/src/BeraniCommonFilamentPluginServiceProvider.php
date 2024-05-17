<?php

namespace BeraniDigitalID\BeraniCommonFilamentPlugin;

use BeraniDigitalID\BeraniCommonFilamentPlugin\Commands\BeraniCommonFilamentPluginCommand;
use BeraniDigitalID\BeraniCommonFilamentPlugin\Testing\TestsBeraniCommonFilamentPlugin;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BeraniCommonFilamentPluginServiceProvider extends PackageServiceProvider
{
    public static string $name = 'berani-common-filament-plugin';

    public static string $viewNamespace = 'berani-common-filament-plugin';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('beranidigital/berani-common-filament-plugin');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            BeraniCommonFilamentPluginCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_berani-common-filament-plugin_table',
        ];
    }

    public function packageRegistered(): void
    {
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/berani-common-filament-plugin/{$file->getFilename()}"),
                ], 'berani-common-filament-plugin-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsBeraniCommonFilamentPlugin());
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('berani-common-filament-plugin', __DIR__ . '/../resources/dist/components/berani-common-filament-plugin.js'),
            Css::make('berani-common-filament-plugin-styles', __DIR__ . '/../resources/dist/berani-common-filament-plugin.css'),
            Js::make('berani-common-filament-plugin-scripts', __DIR__ . '/../resources/dist/berani-common-filament-plugin.js'),
        ];
    }

    protected function getAssetPackageName(): ?string
    {
        return 'beranidigital/berani-common-filament-plugin';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }
}
