<?php

namespace GotChosen\Util;

class Dates
{
    public static function prevMonth($month)
    {
        return date_create($month)->modify('-1 month')->format('Y-m');
    }

    public static function nextMonth($month)
    {
        return date_create($month)->modify('+1 month')->format('Y-m');
    }

    public static function rangeMonths($start, $out)
    {
        $months = [$start];
        if ( $out == 0 ) {
            return $months;
        }

        for ( $i = 0; $i < abs($out); $i++ ) {
            $start = $out < 0 ? self::prevMonth($start) : self::nextMonth($start);
            $months[] = $start;
        }

        return $months;
    }
}