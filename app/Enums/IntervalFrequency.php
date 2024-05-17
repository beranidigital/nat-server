<?php

namespace App\Enums;

enum IntervalFrequency
{
    case Hourly;
    case Daily;
    case Weekly;
    case Monthly;

    public static function from(mixed $frequency)
    {
        return match ($frequency) {
            'Hourly' => self::Hourly,
            'Daily' => self::Daily,
            'Weekly' => self::Weekly,
            'Monthly' => self::Monthly,
            default => self::Monthly,
        };
    }

    public function toTrendInterval(): string
    {
        return match ($this) {
            self::Hourly => 'hour',
            self::Daily => 'day',
            self::Weekly => 'week',
            self::Monthly => 'month',
        };
    }
}
