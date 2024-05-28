<?php
namespace App\Livewire;

use App\Enums\IntervalFrequency;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;

/**
 * @property Form $form
 */
class FilterTimeline extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'livewire.filter-timeline';

    public ?array $data = [];
    public ?string $device = null;

    public function mount(): void
    {
        $data = request()->all();
        if(!isset($data['frequency'])) $data['frequency'] = IntervalFrequency::Daily->name;
        if(!isset($data['start_date'])) $data['start_date'] = Carbon::now()->subDays(7);
        if(!isset($data['end_date'])) $data['end_date'] = Carbon::now();
        $this->form->fill($data);
        $this->device = request()->get('device');
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Select::make('frequency')
                    ->default(IntervalFrequency::Daily->name)
                    ->options(IntervalFrequency::class),
                DatePicker::make('start_date')
                    ->displayFormat('d-m-Y')
                    ->native(false),
                DatePicker::make('end_date')
                    ->displayFormat('d-m-Y')
                    ->native(false)->reactive() // Menambahkan reactive untuk memicu perubahan
                    ->afterStateUpdated(fn () => $this->create()),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        // set GET parameters
        $state = $this->form->getState();
        $device = $this->device;
        $url = request()->header('referer');
        //url without query string
        $parsed_url = parse_url($url);
        $url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . (isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '') . $parsed_url['path'];
        // merge old query string with new state
        $params = array_merge(request()->query(), $state);

        $this->redirect($url . '?'. 'device=' . $device . '&' . http_build_query($params));
    }
}
