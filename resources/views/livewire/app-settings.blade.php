<div>
    <div style="margin: 1%">
        <form wire:submit="submit">
            {{ $this->form }}

            <x-filament::button type="submit" class="mt-4">
                Submit
            </x-filament::button>
        </form>

        <x-filament-actions::modals />
    </div>
</div>
