<?php

namespace App\Libs;

class Time
{
    public static function convert($date, $inputFormat, $outputFormat) {
        $date = \DateTime::createFromFormat($inputFormat, $date);

        return $date->format($outputFormat);
    }

    public static function getasDate($inputFormat, $date) {
        $date = \DateTime::createFromFormat($inputFormat, $date);

        return $date;
    }

    public static function addDays($date, $days) {
        return $date->add(new \DateInterval('P'.$days.'D'));
    }

    public static function addYears($date, $years) {
        return $date->add(new \DateInterval('P'.$years.'Y'));
    }
}
