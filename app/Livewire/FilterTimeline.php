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
        $this->form->fill(request()->all());
        $this->device = request()->get('device');
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Select::make('frequency')
                    ->default(IntervalFrequency::Weekly->name)
                    ->options(IntervalFrequency::class),
                DatePicker::make('start_date')
                    ->default(Carbon::now()->subWeek())
                    ->displayFormat('d-m-Y')
                    ->native(false),
                DatePicker::make('end_date')
                    ->default(Carbon::now()->endOfMonth())
                    ->displayFormat('d-m-Y')
                    ->native(false),
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
