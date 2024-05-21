<?php

    namespace App\Livewire;

    use App\Exports\SensorDataExport;
    use App\Http\Controllers\WaterpoolController;
    use App\Models\Pool\StateLog;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;
    use Filament\Support\Colors\Color;
    use Filament\Tables\Actions\Action;
    use Filament\Widgets\TableWidget;
    use Filament\Tables\Actions\ExportAction;
    use Maatwebsite\Excel\Facades\Excel;
    use Dompdf\Dompdf;
use Filament\Tables\Enums\ActionsPosition;

    class DeviceTable extends TableWidget
    {
        protected static ?string $heading = 'All Items';
        public string $device;
        protected function getFilamentTableColumns(): array
        {
            $allowedSensors = WaterpoolController::getAllowedSensors();
            $customLabels = [
                'battery' => 'Battery',
                'cl' => 'Chlorine',
                'ec' => 'Conductivity',
                'orp' => 'Sanitation(orp)',
                'temp' => 'Temperature',
                'ph' => 'Ph',
                'tds'=> 'TotalDissolvedSolid(tds)',
                'salt'=> 'Salt',
            ];
            // Ambil data state log
            $stateLogs = StateLog::where('device', $this->device)
                ->orderBy('created_at', 'asc')
                ->limit(1 * 24 * 1)
                ->get()
                ->toArray();

            $sensorsData = [];
            if (empty($stateLogs)) {
                return [];
            }

            // Proses data state log dan ekstrak data sensor
            foreach ($stateLogs as $stateLog) {
                foreach ($stateLog['formatted_sensors'] as $sensor => $state) {
                     if (!in_array($sensor, $allowedSensors)) {
                        continue;
                    }
                    if (!isset($sensorsData[$sensor])) {
                        $sensorsData[$sensor] = [];
                    }
                    $sensorsData[$sensor][] = $state['value'];
                }
            }

            $columns = [];
            foreach ($stateLogs[0]['formatted_sensors'] as $sensor => $state) {
                if (!isset($sensorsData[$sensor])) {
                    continue;
                }
                $label = $customLabels[$sensor];

                $col = TextColumn::make($label)
                    ->getStateUsing(fn($record) => $record['formatted_sensors'][$sensor]['value'] ?? null)
                    ->disabled()
                    ->searchable()
                    ->sortable();

                $columns[] = $col;
            }

            return $columns;
        }

        public function table(Table $table): Table
        {
            $stateLogQuery = StateLog::where('device', $this->device)->limit(24);
            return $table
                ->columns(
                    $this->getFilamentTableColumns()
                )
                ->query($stateLogQuery)
                ->headerActions([
                    Action::make('export_excel')
                    ->color(Color::Blue)
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function(){
                        $deviceName = request()->get('device', $this->device);
                        return Excel::download(new SensorDataExport($deviceName), "sensor_data_{$deviceName}.xlsx");
                    }),
                    Action::make('export_pdf')
                    ->color(Color::Blue)
                    ->label('Export PDF')->icon('heroicon-o-document-arrow-down')
                    ->action(function(){
                        $deviceName = request()->get('device', $this->device);
                        return Excel::download(new SensorDataExport($deviceName), "sensor_data_{$deviceName}.pdf");
                    }),
                ])->headerActionsPosition();
        }

    }
