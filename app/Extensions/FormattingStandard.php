<?php

namespace App\Extensions;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class FormattingStandard
{
    public static function defaultLocale()
    {
        return 'id_ID';
    }

    public static function getDateFormat($locale = null)
    {
        return self::getDateTimeFormats()['day'];
    }

    public static function getDateTimeFormats()
    {
        return [
            'second' => 'd/m/Y H:i:s',
            'minute' => 'd/m/Y H:i:00',
            'hour' => 'd/m/Y H:00',
            'day' => 'd/m/Y',
            'week' => 'W-Y',
            'month' => 'm-Y',
            'year' => 'Y',
        ];
    }

    public static function getDateFormatForDatabase(): array
    {
        return [
            'second' => '%d/%m/%Y %H:%i:%s',
            'minute' => '%d/%m/%Y %H:%i:00',
            'hour' => '%d/%m/%Y %H:00',
            'day' => '%d/%m/%Y',
            'week' => 'W%V-%X',
            'month' => '%m-%Y',
            'year' => '%Y',
        ];
    }

    public static function defaultTimezone()
    {
        return 'Asia/Jakarta';
    }

    public static function getDateTimeFormat($locale = null)
    {
        return self::getDateTimeFormats()['second'];
    }

    public static function tryFormatThisDate(string|CarbonInterface|\DateTime|null $date, $format = null): string
    {
        if (is_null($format)) {
            $format = self::getDateFormat();
        }
        if ($date instanceof CarbonInterface) {
            $date->setTimezone(new \DateTimeZone(self::defaultTimezone()));
            return $date->format($format);
        }
        if ($date instanceof \DateTime) {
            $date->setTimezone(new \DateTimeZone(self::defaultTimezone()));
            return $date->format($format);
        }
        try {
            $date = Carbon::parse($date);
            $date->setTimezone(new \DateTimeZone(self::defaultTimezone()));
            return $date->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }

    public static function tryFormatThisDateTime(string|CarbonInterface|\DateTime|null $date, $format = null): string
    {
        return self::tryFormatThisDate($date, $format ?? self::getDateTimeFormat()) . ' WIB';
    }


}
