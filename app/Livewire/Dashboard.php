<?php

namespace App\Livewire;

use App\Filament\Pages\PoolDetail;
use App\Http\Controllers\WaterpoolController;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Support\Colors\Color;

class Dashboard extends BaseWidget //extends Page implements HasInfolists
{
    // use InteractsWithInfolists;

    public $data;

    protected function getStats(): array
    {
        $devices = AppSettings::getDevicesName()->value;
        $deviceSections = [];
        $allowedSensors = WaterpoolController::getAllowedSensors();
        foreach ($devices as $device => $friendlyName) {
            $stateLog = StateLog::where('device', $device)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($stateLog) {
                $sensorData = $stateLog->toArray();
                $formattedStatus = $sensorData['scores'];
                $formattedBattery = $sensorData['sensors']['battery'];
                $formattedSensor = $sensorData['formatted_sensors'];
                $labelMappings = $formattedSensor;

                $sections = [];
                $phColor = null;
                $orpColor = null;

                foreach ($formattedStatus as  $key => $color) {
                    $label = $labelMappings[$key]['label'] ?? $key;
                    if (!in_array($key, $allowedSensors)) {
                        continue;
                    }
                    if ($color >= AppSettings::$greenScoreMin && $color < AppSettings::$greenScoreMax) {
                        $iconColor =  Color::Emerald;
                    } elseif ($color >= AppSettings::$yellowScoreMin && $color < AppSettings::$yellowScoreMax) {
                        $iconColor = Color::Yellow;
                    } elseif ($formattedSensor[$key]['value'] === 'unknown' || $formattedSensor[$key]['value'] == 'unavailable') {
                        $iconColor = Color::Gray;
                    } else{
                        $iconColor = Color::Red;
                    }

                    if ($key === 'ph') {
                        $phColor = $iconColor;
                    } elseif ($key === 'orp') {
                        $orpColor = $iconColor;
                    }

                    $statString = TextEntry::make('')
                        ->getStateUsing($label)
                        ->icon('heroicon-s-stop')
                        ->iconColor($iconColor);

                    if($formattedSensor[$key]['value'] == 'unknown' || $formattedSensor[$key]['value'] == 'unavailable')
                    {
                        $formattedSensor[$key]['value'] = '-';
                        $formattedSensor[$key]['unit'] = '';
                    }

                    $statString1 = TextEntry::make('')
                        ->getStateUsing($formattedSensor[$key]['value'] . ' '. $formattedSensor[$key]['unit'])
                        ->color($iconColor)
                        ->alignEnd()
                        ->grow(false);
                    $splitText = Split::make([$statString,$statString1]);

                    $sections[] = $splitText;
                }
                // Determine image URL based on PH and ORP colors
                $imageUrl = '';
                if ($phColor === Color::Emerald && $orpColor === Color::Emerald) {
                    $imageUrl = url('images/green.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing('Good: Water with optimal pH and proper ORP values is considered safe and conducive to health.')
                        ->color(Color::Emerald)
                        ->alignCenter();
                } elseif (($phColor === Color::Red && $orpColor  === Color::Red)) {
                    $imageUrl = url('images/red.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing('Bad: Water with suboptimal pH and ORP values may pose risks to health and water quality.')
                        ->color(Color::Red)
                        ->alignCenter();
                } elseif ($orpColor  === Color::Red) {
                    $imageUrl = url('images/red.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing('Bad: Water with suboptimal ORP value may pose risks to health and water quality.')
                        ->color(Color::Red)
                        ->alignCenter();
                } elseif ($phColor === Color::Red) {
                    $imageUrl = url('images/red.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing('Bad: Water with suboptimal pH value may pose risks to health and water quality.')
                        ->color(Color::Red)
                        ->alignCenter();
                } elseif (($phColor === Color::Yellow || $orpColor  === Color::Yellow)) {
                    $imageUrl = url('images/yellow.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing('Caution: Water with suboptimal pH and ORP values may pose risks to health and water quality.')
                        ->color(Color::Gray)
                        ->alignCenter();
                } else {
                    $imageUrl = url('images/gray.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing('Nonaktif')
                        ->color(Color::Gray)
                        ->alignCenter();
                }

                $imageEntry = ImageEntry::make('')
                    ->size(80)
                    ->defaultImageUrl($imageUrl)
                    ->extraAttributes(['style' => 'margin-left:40%;']);
                $friendlyNameEntry = TextEntry::make('')->getStateUsing($friendlyName)->size('lg')->weight(FontWeight::Bold);

                if(!is_numeric($formattedBattery)){
                    $textBattery = $formattedBattery;
                } else {
                    $textBattery = $formattedBattery . '%';
                }
                if($formattedBattery > 70 && $formattedBattery <= 100){
                     $battery = TextEntry::make('')->getStateUsing($textBattery)
                    ->icon('heroicon-s-battery-100')->alignEnd()->iconColor(Color::Emerald);
                } else if($formattedBattery > 25 && $formattedBattery < 70){
                     $battery = TextEntry::make('')->getStateUsing($textBattery)
                    ->icon('heroicon-s-battery-50')->alignEnd()->iconColor(Color::Yellow);
                } else if($formattedBattery == 'unknown' || $formattedBattery == 'unavailable'){
                    $battery = TextEntry::make('')->getStateUsing('-')
                   ->icon('heroicon-s-battery-0')->alignEnd()->iconColor(Color::Gray);
                } else {
                     $battery = TextEntry::make('')->getStateUsing('-')
                    ->icon('heroicon-s-battery-0')->alignEnd()->iconColor(Color::Red);
                }
                $friendlyNameSection = Split::make([$friendlyNameEntry,$battery]);
                $sections = array_merge([$friendlyNameSection], [$imageEntry], [$iconStatus], $sections);
                if($formattedBattery == 'unknown' || $formattedBattery == 'unavailable' || $phColor === Color::Gray || $orpColor  === Color::Gray){
                    $section = Section::make($sections)->extraAttributes(['onclick' => "window.location.href='admin/pool-detail?device=$device'", 'style' => 'cursor: pointer;background-color: rgb(243, 244, 246)']);
                    $deviceSections[$friendlyName] = $section;
                } else{
                    $section = Section::make($sections)->extraAttributes(['onclick' => "window.location.href='admin/pool-detail?device=$device'", 'style' => 'cursor: pointer;']);
                    $deviceSections[$friendlyName] = $section;
                }
            }
        }
        $infolists = [];
        foreach ($deviceSections as $section) {
            $infolist = Infolist::make()->schema([$section])->record(StateLog::query()->first());
            $infolists[] = $infolist;
        }

        return $infolists;
    }

}
