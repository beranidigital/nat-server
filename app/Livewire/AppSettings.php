<?php

namespace App\Livewire;

use App\Models\AppSettings as ModelAppSettings;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Livewire\Component;

class AppSettings extends Component implements HasForms
{
    use InteractsWithForms;
    public ?array $data = [];

    /**
     * Checks if the provided array has string keys.
     *
     * This function counts the number of keys in the array that are strings.
     * If the count is greater than 0, it returns true, indicating that the array has string keys.
     * Otherwise, it returns false.
     *
     * @param array $array The array to check for string keys.
     * @return bool True if the array has string keys, false otherwise.
     */
    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData()
    {
        $devices = ModelAppSettings::getDevicesName()->value;
        $translation = ModelAppSettings::getTranslation()->value;
        $message = ModelAppSettings::getMessage()->value;

        asort($devices);
        if (is_array($devices)) {
            foreach ($devices as $deviceKey => $deviceValue) {
                    $this->data['devices'][$deviceKey] = $deviceValue;
            }
        }

        if (is_array($translation)) {
            foreach ($translation as $translationKey => $translationValue) {
                $this->data['translation'][$translationKey] = $translationValue;
            }
        }
        if (is_array($message)) {
            foreach ($message as $messageKey => $messageValue) {
                $this->data['message'][$messageKey] = $messageValue;
            }
        }
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    Section::make(
                        [...$this->commonSchema(),]
                    ),
                ]),
            ])
            ->statePath('data');
    }

    public function commonSchema(): array
    {
        $formattedDevices = $this->data['devices'];
        $formattedTranslation = $this->data['translation'];
        $formattedMessage = $this->data['message'];
        $messageKeyLabel = [
            'good' => ' Good Condition Message',
            'caution' => 'Caution Condition Message',
            'bad' => 'Bad ORP & pH Condition Message',
            'badOrp' => 'Bad ORP Condition Message',
            'badPh' => 'Bad pH Condition Message',
            'disabled' => 'Caution Condition Message'

        ];
        $sections = [];

        $deviceInputs = [];
        foreach ($formattedDevices as $deviceKey => $deviceValue) {
            $deviceInputs[] = TextInput::make('devices.' . $deviceKey)
                ->label('Name for ' .$deviceKey)
                ->required();
        }
        $sections[] = Section::make('Device Name')
        ->schema($deviceInputs);

        $translationInputs = [];
        foreach ($formattedTranslation as $translationKey => $translationValue) {
            $translationInputs[] = TextInput::make('translation.' . $translationKey)
                ->label($translationKey)
                ->required();
        }
        $sections[] = Section::make('Sensor Name')
        ->schema($translationInputs);

        $messageInputs = [];
        foreach ($formattedMessage as $messageKey => $messageValue) {
            $messageLabel = $messageKeyLabel[$messageKey] ?? $messageKey;
            $messageInputs[] = TextInput::make('message.' . $messageKey)
                ->label($messageLabel)
                ->required();
        }
        $sections[] = Section::make('Status Message')
            ->schema($messageInputs);

        return $sections;
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $dataToUpdate = [
            'devices_name' => $state['devices'],
            'translation' => $state['translation'],
            'message' => $state['message'],
        ];
        foreach ($dataToUpdate as $key => $value) {
            ModelAppSettings::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        \Filament\Notifications\Notification::make()
            ->title('AppSettings updated successfully.')
            ->success()
            ->send();

        $this->loadData();
    }




    public function render()
    {
        return view('livewire.app-settings');
    }
}
