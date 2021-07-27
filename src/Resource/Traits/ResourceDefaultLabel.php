<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Illuminate\Support\Str;
use ReflectionClass;

trait ResourceDefaultLabel
{
    /**
     * Remove this from label
     *
     * @var string
     */
    static $labelToReplace = 'Resource';

    /**
     * @return string
     */
    public static function label(): string
    {
        $name = (new ReflectionClass(static::class))->getShortName();
        $name = Str::snake($name, ' ');
        $name = Str::title($name);
        $name = Str::replace(static::$labelToReplace, '', $name);
        $name = trim($name);
        $name = __($name);

        return $name;
    }
}
