<x-filament-widgets::widget>
    <x-filament::section>
        <form wire:submit="create" wire:change="create">
            {{ $this->form }}
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
