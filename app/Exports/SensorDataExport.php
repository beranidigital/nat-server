<?php

namespace App\Exports;

use App\Models\AppSettings;
use App\Models\Pool\StateLog;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class SensorDataExport implements FromQuery, ShouldAutoSize, WithEvents, WithMapping, WithHeadings
{

    use Exportable, RegistersEventListeners;
    protected $deviceName;


    public function __construct(string $deviceName)
    {
        $this->deviceName = $deviceName;
    }


    public static function afterSheet(AfterSheet $event)
    {

        $event->getSheet()->getDelegate()->getStyle('A1:Z1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->getSheet()->getDelegate()->getStyle('A1:Z1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    public function query()
    {
        return StateLog::orderBy('created_at', 'desc')->where('device', $this->deviceName)->paginate(1000);
    }


    /**
     * @param StateLog $row
     */
    public function map($row): array
    {
        $data = [];
        foreach ($row->formatted_sensors as $sensor => $value) {
            $data[] = $value['value'];
        }
        $data[] = Date::dateTimeToExcel($row->created_at);
        return $data;
    }

    public function headings(): array
    {
        $sample = StateLog::where('device', $this->deviceName)->orderBy('created_at', 'desc')->first();
        $sensors = $sample->formatted_sensors ?? [];
        $sensors = array_keys($sensors);
        $translated = [];
        foreach ($sensors as $sensor) {
            $translated[] = AppSettings::translateSensorKey($sensor);
        }
        $translated[] = 'Timestamp';
        return $translated;
    }
}
