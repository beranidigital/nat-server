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
    public static function has_string_keys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    public static function kvToArray($kv)
    {
        if (!self::has_string_keys($kv)) {
            return $kv;
        }
        $array = [];
        foreach ($kv as $key => $value) {
            // check if value is Key Value
            if (is_array($value)) {
                $array[] = [
                    'name' => $key,
                    'value' => self::kvToArray($value),
                ];
                continue;
            }
            $array[] = [
                'name' => $key,
                'value' => $value,
            ];
        }

        return $array;
    }

    public static function arrayToKv($input)
    {
        $output = [];

        if (isset($input['name']) && isset($input['value'])) {
            $output[$input['name']] = $input['value'];
        } else {
            foreach ($input as $item) {
                if (!isset($item['name']) || !isset($item['value'])) {
                    $output[] = $item;
                } else if (is_array($item['value'])) {
                    $output[$item['name']] = self::arrayToKv($item['value']);
                } else {
                    $output[$item['name']] = $item['value'];
                }
            }
        }

        return $output;
    }

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
            $messageInputs[] = TextInput::make('message.' . $messageKey)
                ->label($messageKey)
                ->required();
        }
        $sections[] = Section::make('Sensor Message')
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
