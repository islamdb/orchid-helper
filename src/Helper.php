<?php

namespace IslamDB\OrchidHelper;

use Carbon\Carbon;

class Helper
{
    /**
     * @param $array
     * @param string $splitter
     * @param string $lastSplitter
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public static function arrayToString($array, string $splitter = ', ', string $lastSplitter = ' and ', string $prefix = '', string $suffix = ''): string
    {
        $string = implode($splitter, $array);
        $string = str_replace($splitter . last($array), $lastSplitter . last($array), $string);

        return $prefix . $string . $suffix;
    }

    /**
     * @param $datetime
     * @param string $locale
     * @param bool $withTime
     * @param bool $withDayName
     * @return string
     */
    public static function readableDatetime($datetime, string $locale = 'id', bool $withTime = true, bool $withDayName = true)
    {
        $format = 'dddd, MMMM Do YYYY, HH:mm:ss';
        $localeFormats = [
            'jv' => 'dddd, DD MMMM YYYY, HH:mm:ss',
            'id' => 'dddd, DD MMMM YYYY, HH:mm:ss',
            'en' => 'dddd, MMMM Do YYYY, HH:mm:ss'
        ];
        $format = $localeFormats[$locale] ?? $format;
        $format = !$withTime
            ? str_replace(', HH:mm:ss', '', $format)
            : $format;
        $format = !$withDayName
            ? str_replace('dddd, ', '', $format)
            : $format;

        return Carbon::parse($datetime)
            ->locale($locale)
            ->isoFormat($format);
    }

    /**
     * @param $object
     * @param $methods
     * @return mixed
     */
    public static function chainedMethodCall($object, $methods)
    {
        $callStr = 'return $object->';
        foreach($methods as $method => $param){
            $callStr.= "$method($param)->";
        }
        $callStr = substr($callStr, 0, -2);
        $callStr.= ';';

        return eval($callStr);
    }
}
