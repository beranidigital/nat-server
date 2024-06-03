<?php

namespace App\Livewire;

use App\Filament\Pages\PoolDetail;
use App\Http\Controllers\WaterpoolController;
use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Support\Colors\Color;

class Dashboard extends BaseWidget
{
    public $data;

    protected function getStats(): array
    {
        $devices = AppSettings::getDevicesName()->value;
        asort($devices);
        $deviceSections = [];
        $allowedSensors = WaterpoolController::getAllowedSensors();
        $unknownSensorValues = array_column(WaterpoolController::unknownSensors(), 'value');
        $unavailableSensorValues = array_column(WaterpoolController::unavailable(), 'value');
        $message = AppSettings::getMessage()->value;

        foreach ($devices as $device => $friendlyName) {
            $stateLog = StateLog::where('device', $device)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($stateLog) {
                $sensorData = $stateLog->toArray();
                $formattedStatus = $sensorData['scores'];
                $formattedBattery = $sensorData['sensors']['battery'];
                $formattedSensor = $sensorData['formatted_sensors'];
                $messageSensor = $message;
                $labelMappings = $formattedSensor;

                $sections = [];
                $phColor = null;
                $orpColor = null;
                $allUnknown = true;
                foreach ($formattedStatus as $key => $color) {
                    if (!in_array($key, $allowedSensors)) {
                        continue;
                    }
                    if (!in_array($formattedSensor[$key]['value'], $unknownSensorValues) &&
                        !in_array($formattedSensor[$key]['value'], $unavailableSensorValues)) {
                        $allUnknown = false;
                        break;
                    }
                }
                if ($allUnknown) {
                    $imageUrl = url('images/gray.png');
                    $iconStatus = TextEntry::make('')
                        ->getStateUsing($messageSensor['disabled'])
                        ->color(Color::Gray)
                        ->alignCenter();
                    $iconColor = Color::Gray;
                    foreach ($formattedStatus as $key => $color) {
                        $label = $labelMappings[$key]['label'] ?? $key;
                        if (!in_array($key, $allowedSensors)) {
                            continue;
                        }
                        if (in_array($formattedSensor[$key]['value'], $unknownSensorValues) ||
                            in_array($formattedSensor[$key]['value'], $unavailableSensorValues)) {
                            $formattedSensor[$key]['value'] = '-';
                            $formattedSensor[$key]['unit'] = '';
                        }
                        $statString = TextEntry::make('')
                            ->getStateUsing($label)
                            ->icon('heroicon-s-stop')
                            ->iconColor($iconColor);
                        $statString1 = TextEntry::make('')
                            ->getStateUsing($formattedSensor[$key]['value'] . ' ' . $formattedSensor[$key]['unit'])
                            ->color($iconColor)
                            ->alignEnd()
                            ->grow(false);
                        $splitText = Split::make([$statString, $statString1]);

                        $sections[] = $splitText;
                    }
                    $imageEntry = ImageEntry::make('')
                        ->size(80)
                        ->defaultImageUrl($imageUrl)
                        ->extraAttributes(['style' => 'margin-left:40%;']);
                    $friendlyNameEntry = TextEntry::make('')->getStateUsing($friendlyName)->size('lg')->weight(FontWeight::Bold);
                    if (!is_numeric($formattedBattery)) {
                        $textBattery = 'N/A';
                    } else {
                        $textBattery = $formattedBattery . '%';
                    }

                    $battery = TextEntry::make('')->getStateUsing($textBattery)
                        ->icon('heroicon-s-battery-0')->alignEnd()->iconColor(Color::Gray);

                    $friendlyNameSection = Split::make([$friendlyNameEntry, $battery]);
                    $sections = array_merge([$friendlyNameSection], [$imageEntry], [$iconStatus], $sections);

                    $backgroundColor = 'background-color: rgb(243, 244, 246)' ;
                    $section = Section::make($sections)
                        ->extraAttributes([
                            'onclick' => "window.location.href='admin/pool-detail?device=$device'",
                            'style' => "cursor: pointer; $backgroundColor"
                        ]);

                    $deviceSections[$friendlyName] = $section;
                } else {
                    foreach ($formattedStatus as $key => $color) {
                        $label = $labelMappings[$key]['label'] ?? $key;

                        if (!in_array($key, $allowedSensors)) {
                            continue;
                        }
                        if ($color >= AppSettings::$greenScoreMin && $color < AppSettings::$greenScoreMax) {
                            $iconColor = Color::Emerald;
                        } elseif ($color >= AppSettings::$yellowScoreMin && $color < AppSettings::$yellowScoreMax) {
                            $iconColor = Color::Yellow;
                        } else {
                            $iconColor = Color::Red;
                        }
                        if ($key === 'ph') {
                            $phColor = $iconColor;
                        } elseif ($key === 'orp') {
                            $orpColor = $iconColor;
                        }

                        if (in_array($formattedSensor[$key]['value'], $unknownSensorValues) ||
                            in_array($formattedSensor[$key]['value'], $unavailableSensorValues)) {
                            $formattedSensor[$key]['value'] = '-';
                            $formattedSensor[$key]['unit'] = '';
                        }

                        $statString = TextEntry::make('')
                            ->getStateUsing($label)
                            ->icon('heroicon-s-stop')
                            ->iconColor($iconColor);
                        $statString1 = TextEntry::make('')
                            ->getStateUsing($formattedSensor[$key]['value'] . ' ' . $formattedSensor[$key]['unit'])
                            ->color($iconColor)
                            ->alignEnd()
                            ->grow(false);
                        $splitText = Split::make([$statString, $statString1]);

                        $sections[] = $splitText;


                        if ($phColor === Color::Emerald && $orpColor === Color::Emerald) {
                            $imageUrl = url('images/green.png');
                            $iconStatus = TextEntry::make('')
                                ->getStateUsing($messageSensor['good'])
                                ->color(Color::Emerald)
                                ->alignCenter();
                        } elseif (($phColor === Color::Red && $orpColor === Color::Red)) {
                            $imageUrl = url('images/red.png');
                            $iconStatus = TextEntry::make('')
                                ->getStateUsing($messageSensor['bad'])
                                ->color(Color::Red)
                                ->alignCenter();
                        } elseif ($orpColor === Color::Red) {
                            $imageUrl = url('images/red.png');
                            $iconStatus = TextEntry::make('')
                                ->getStateUsing($messageSensor['badOrp'])
                                ->color(Color::Red)
                                ->alignCenter();
                        } elseif ($phColor === Color::Red) {
                            $imageUrl = url('images/red.png');
                            $iconStatus = TextEntry::make('')
                                ->getStateUsing($messageSensor['badPh'])
                                ->color(Color::Red)
                                ->alignCenter();
                        } elseif (($phColor === Color::Yellow || $orpColor === Color::Yellow)) {
                            $imageUrl = url('images/yellow.png');
                            $iconStatus = TextEntry::make('')
                                ->getStateUsing($messageSensor['caution'])
                                ->color(Color::Gray)
                                ->alignCenter();
                        } else {
                            $imageUrl = url('images/red.png');
                            $iconStatus = TextEntry::make('')
                                ->getStateUsing($messageSensor['bad'])
                                ->color(Color::Red)
                                ->alignCenter();
                        }
                    }

                    $imageEntry = ImageEntry::make('')
                        ->size(80)
                        ->defaultImageUrl($imageUrl)
                        ->extraAttributes(['style' => 'margin-left:40%;']);
                    $friendlyNameEntry = TextEntry::make('')->getStateUsing($friendlyName)->size('lg')->weight(FontWeight::Bold);

                    if (!is_numeric($formattedBattery)) {
                        $textBattery = $formattedBattery;
                    } else {
                        $textBattery = '0' . '%';
                    }

                    if ($formattedBattery > 70 && $formattedBattery <= 100) {
                        $battery = TextEntry::make('')->getStateUsing($textBattery)
                            ->icon('heroicon-s-battery-100')->alignEnd()->iconColor(Color::Emerald);
                    } elseif ($formattedBattery > 25 && $formattedBattery < 70) {
                        $battery = TextEntry::make('')->getStateUsing($textBattery)
                            ->icon('heroicon-s-battery-50')->alignEnd()->iconColor(Color::Yellow);
                    } else {
                        $battery = TextEntry::make('')->getStateUsing('-')
                            ->icon('heroicon-s-battery-0')->alignEnd()->iconColor(Color::Red);
                    }

                    $friendlyNameSection = Split::make([$friendlyNameEntry, $battery]);
                    $sections = array_merge([$friendlyNameSection], [$imageEntry], [$iconStatus], $sections);

                    $backgroundColor = 'background-color: white';
                    $section = Section::make($sections)
                        ->extraAttributes([
                            'onclick' => "window.location.href='admin/pool-detail?device=$device'",
                            'style' => "cursor: pointer; $backgroundColor"
                        ]);

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
