<?php

namespace App\Livewire;

use App\Http\Controllers\WaterpoolController;
use App\Models\AppSettings;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

/**
 * @property Form form
 */
class SettingsParameter extends Component implements HasForms
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
        $this->data = [
            'parameter_profile' => self::kvToArray(AppSettings::getParameterProfile()),
            'pool_profile_parameter' => self::kvToArray(AppSettings::getPoolProfileParameter()),
        ];
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pool Parameter')->schema(([
                    Repeater::make('pool_profile_parameter')
                        ->hiddenLabel()
                        ->reorderableWithDragAndDrop(false)
                        ->addable(false)
                        ->deletable(false)
                        ->schema([
                            TextInput::make('name')
                                ->label('Pool Name')
                                ->required(),
                            Select::make('value')
                                ->label('Parameter Profile')
                                ->options($this->getParameters())
                        ])
                ])),
                Section::make('Parameter Profile')->schema([

                Repeater::make('parameter_profile')
                    ->hiddenLabel()
                    ->reorderableWithDragAndDrop(false)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)

                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('Name'),
                        Repeater::make('value')
                            ->label("Parameter")
                            ->columns(7)
                            ->schema([
                                Select::make('sensor')->options($this->getSensors()),
                                TextInput::make('start')
                                    ->required()
                                    ->label('Start')
                                    ->numeric(),
                                TextInput::make('cs')
                                    ->label('Caution Start')
                                    ->required()
                                    ->numeric(),
                                TextInput::make('gs')
                                    ->required()
                                    ->numeric()
                                    ->label('Good Start'),
                                TextInput::make('ge')
                                    ->required()
                                    ->label('Good End')
                                    ->numeric(), // less than or equal
                                TextInput::make('ce')
                                    ->required()
                                    ->label('Caution End')
                                    ->numeric(), // greater than or equal
                                TextInput::make('end')
                                    ->required()
                                    ->numeric()
                                    ->label('End'),
                            ])
                    ])->reorderable(false)->collapsible()->collapsed()
                ]),

            ])
            ->statePath('data');
    }

    public function getSensors()
    {
        $sensors = [];
        $formattedSensor = AppSettings::getTranslation()->value;
        $allowedSensors = WaterpoolController::getAllowedSensors();
        foreach ($formattedSensor as $key => $value) {
             if (!in_array($key, $allowedSensors)) {
                    continue;
                }
            $sensors[$key] = __($value);
        }
        return $sensors;
    }

    public function getParameters()
    {
        $parameters = [];
        foreach (AppSettings::getParameterProfile() as $parameter => $value) {
            $parameters[$parameter] = $parameter;
        }
        return $parameters;
    }

    public function create(): void
    {
        $state = $this->form->getState();
        $stateArrayed = [];
        foreach ($state as $key => $value) {
            $stateArrayed[$key] = self::arrayToKv($value);
        }

        foreach ($stateArrayed as $key => $value) {
            AppSettings::updateOrCreate([
                'key' => $key,
            ], [
                'value' => $value,
            ]);
        }
        \Filament\Notifications\Notification::make()->title('Parameter profile updated successfully.')->success()->send();
        // tell to reload
        if (request()->hasHeader('Referer'))
            redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.settings-parameter');
    }
}
