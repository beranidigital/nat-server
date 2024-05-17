<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Component;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('clear_cache')
                ->label(__('Clear Cache'))
                ->icon('heroicon-o-arrow-path')
                ->action(function (Component $livewire) {
                    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                    Notification::make()
                        ->title(__('Permissions Refreshed'))
                        ->success()
                        ->send();
                })
        ];
    }
}
